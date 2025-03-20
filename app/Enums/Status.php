<?php

namespace App\Enums;

enum Status: string
{
    case SUCCESS = 'success';
    case FAIL = 'fail';
    case SSL_ISSUE = 'ssl_issue';
    case SSL_EXPIRED = 'ssl_expired';
    case SSL_EXPIRING_SOON = 'ssl_expiring_soon';
} 