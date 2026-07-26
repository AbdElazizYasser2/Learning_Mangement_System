<?php

namespace App\Enums;

enum UserRole: string
{
    case USER = 'user';
    case INSTRUCTOR = 'instructor';
    case ADMIN = 'admin';
}
