<?php

namespace Webkul\Security\Models;

use App\Models\User as BaseUser;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\Email\Concerns\InteractsWithEmailAuthentication;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use Webkul\Employee\Models\Department;
use Webkul\Employee\Models\Employee;
use Webkul\Partner\Models\Partner;
use Webkul\Security\Enums\PermissionType;
use Webkul\Security\Support\OwnerSource;
use Webkul\Security\Traits\HasOwnershipScope;
use Webkul\Support\Models\Company;
use Webkul\Support\Models\Scopes\CompanyScope;

class User extends BaseUser implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, HasAvatar, HasEmailAuthentication
{
    use HasOwnershipScope,
        HasRoles,
        InteractsWithAppAuthentication,
        InteractsWithAppAuthenticationRecovery,
        InteractsWithEmailAuthentication,
        SoftDeletes;

    public function __construct(array $attributes = [])
    {
        $this->mergeFillable([
            'partner_id',
            'language',
            'creator_id',
            'is_active',
            'default_company_id',
            'resource_permission',
            'is_default',
        ]);

        $this->mergeCasts([
            'default_company_id'  => 'integer',
            'resource_permission' => PermissionType::class,
            'is_default'          => 'boolean',
            'is_active'           => 'boolean',
            'password'            => 'hashed',
        ]);

        parent::__construct($attributes);
    }

    protected static function ownershipScopeIsGlobal(): bool
    {
        return false;
    }

    public function ownershipSources(): array
    {
        return [
            OwnerSource::column('creator_id'),
            OwnerSource::column('id'),
        ];
    }

    protected $guard_name = ['web', 'sanctum'];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function getAvatarUrlAttribute(): ?string
    {
        $avatar = $this->partner?->avatar ?? $this->avatar;

        if (! $avatar) {
            return null;
        }

        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            return $avatar;
        }

        return Storage::disk('public')->url($avatar);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->getAvatarUrlAttribute();
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'user_team', 'user_id', 'team_id');
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'manager_id');
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id')
            ->withoutGlobalScope(CompanyScope::class);
    }

    public function allowedCompanies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'user_allowed_companies', 'user_id', 'company_id');
    }

    public function defaultCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'default_company_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->creator_id ??= Auth::id();
        });

        static::saved(function ($user) {
            if (! $user->partner_id) {
                $user->handlePartnerCreation($user);
            } else {
                $user->handlePartnerUpdation($user);
            }
        });
    }

    private function handlePartnerCreation(self $user): void
    {
        $partner = Partner::withoutGlobalScopes()->create([
            'creator_id'   => Auth::id() ?? $user->id,
            'company_id'   => $user->default_company_id ?? 1,
            'user_id'      => $user->id,
            'account_type' => 'individual',
            'sub_type'     => 'partner',
            'name'         => $user->name,
            'email'        => $user->email,
            'avatar'       => $user->attributes['avatar'] ?? null,
        ]);

        $user->partner_id = $partner->id;
        $user->saveQuietly();
    }

    private function handlePartnerUpdation(self $user): void
    {
        if (! $user->partner_id) {
            $this->handlePartnerCreation($user);

            return;
        }

        $updateData = [
            'name'       => $user->name,
            'email'      => $user->email,
            'company_id' => $user->default_company_id ?? 1,
            'user_id'    => $user->id,
        ];

        if (array_key_exists('avatar', $user->attributes) && ! empty($user->attributes['avatar'])) {
            $updateData['avatar'] = $user->attributes['avatar'];
        }

        Partner::withoutGlobalScopes()->where('id', $user->partner_id)->update($updateData);
    }
}
