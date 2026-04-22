<?php

namespace App\Exceptions;

use Exception;

class NotUserRentalException extends Exception
{
    public function status()
    {
        return FORBIDDEN;
    }

    public function message()
    {
        return 'Le client n\'a pas loué cet équipement.';
    }
}
