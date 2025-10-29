<?php

namespace iutnc\deefy\exception;

use Exception;

class InvalidPropertyNameException extends Exception {
    public function __construct(string $property, int $code = 0, Exception $previous = null) {
        parent::__construct("Propriété inconnue : $property", $code, $previous);
    }
}