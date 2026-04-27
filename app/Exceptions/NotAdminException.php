<?php

namespace App\Exceptions;

use Exception;

class NotAdminException extends Exception
{
    public function status()
    {
        return FORBIDDEN;
    }

    public function message()
    {
        return 'Seulement les admin peuvent utiliser ces routes.';
    }
}
