<?php

namespace iutnc\deefy\exception;

use Exception;

class InvalidPropertyValueException extends Exception {
    public function __construct(string $property, mixed $value, int $code = 0, Exception $previous = null) {
        parent::__construct("Valeur invalide pour $property : $value", $code, $previous);
    }
}