<?php

namespace App\Exceptions;

use Exception;

class EquipmentInUseException extends Exception
{
    public function status()
    {
        return CONFLICT;
    }

    public function message()
    {
        return 'Equipment is in used and cannot be deleted.';
    }
}
