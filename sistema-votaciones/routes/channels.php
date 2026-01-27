<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('voting-results', function () {
    return true;
});
