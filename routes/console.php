<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('nursery:update-statuses')->dailyAt('00:05');
