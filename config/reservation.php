<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public Reservation Throttle
    |--------------------------------------------------------------------------
    |
    | Maximum number of reservation submissions accepted per minute from a
    | single IP address. Production keeps the safe default, while development
    | and e2e environments raise it so automated QA runs are repeatable.
    |
    */

    'throttle_per_minute' => (int) env('RESERVATION_THROTTLE_PER_MINUTE', 5),

];
