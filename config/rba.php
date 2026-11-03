<?php

return [

    /*
    |--------------------------------------------------------------------------
    | RBA Feature Flag
    |--------------------------------------------------------------------------
    |
    | Modul RBA (Rencana Bisnis Anggaran) adalah fitur tambahan yang terisolasi.
    | Set RBA_ENABLED=false untuk menonaktifkan seluruh modul RBA tanpa
    | memengaruhi fitur lama yang sudah berjalan.
    |
    */

    'enabled' => (bool) env('RBA_ENABLED', true),

];
