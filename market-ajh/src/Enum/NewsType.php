<?php

namespace App\Enum;

enum NewsType: string
{
    case STAFF = 'Communication du staff SAO';
    case FEATURE = 'Nouvelle fonctionnalité';
    case MAINTENANCE = 'Note de maintenance';
}
