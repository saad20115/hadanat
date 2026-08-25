<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        if (! $this->app) {
            $this->refreshApplication();
        }

        if (! class_exists('TestBootstrapHelper')) {
            require_once base_path('plugins/webkul/support/tests/Helpers/TestBootstrapHelper.php');
        }

        \TestBootstrapHelper::ensureERPInstalled();

        parent::setUp();
    }
}
