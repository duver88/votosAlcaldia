<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('voting:check-schedule')->everyMinute();
