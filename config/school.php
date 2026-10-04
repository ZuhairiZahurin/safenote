<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Classes at SMK Pandan Indah
    |--------------------------------------------------------------------------
    |
    | Every class in the school is a form level paired with a class name, for
    | example "4 Amanah". Keeping the list here means the teacher, student and
    | referral forms all offer exactly the same options, so a class cannot be
    | mistyped into existence.
    |
    */

    'forms' => [1, 2, 3, 4, 5],

    'class_names' => [
        'Imtiyaz',
        'Karisma',
        'Cekal',
        'Intelek',
        'Bestari',
        'Amanah',
        'Gigih',
        'Dinamik',
    ],

    /*
    |--------------------------------------------------------------------------
    | Who to contact about a forgotten password
    |--------------------------------------------------------------------------
    |
    | SafeNote does not email password reset links. A counselling account opens
    | confidential student records, so the holder is identified in person before
    | the Admin issues a new password. These details are what the password help
    | page tells staff, and the school changes them here.
    |
    */

    'support' => [
        'office' => env('SCHOOL_SUPPORT_OFFICE', 'Pejabat Pentadbiran, Aras 1, Blok A'),
        'contact' => env('SCHOOL_SUPPORT_CONTACT', 'System Administrator'),
        'email' => env('SCHOOL_SUPPORT_EMAIL', 'admin@safenote.test'),
        'phone' => env('SCHOOL_SUPPORT_PHONE', '03-4291 0000'),
        'hours' => env('SCHOOL_SUPPORT_HOURS', 'Isnin - Jumaat, 8:00 pagi - 4:00 petang'),
    ],

];
