<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Jendela Check-in
    |--------------------------------------------------------------------------
    |
    | Check-in dibuka mulai sekian jam sebelum event dimulai sampai event
    | berakhir (C7). Nilai ini sengaja dibuat sebagai konfigurasi agar mudah
    | diubah tanpa menyentuh logika bisnis.
    |
    */

    'checkin_window_hours' => (int) env('GATHERLY_CHECKIN_WINDOW_HOURS', 2),

];
