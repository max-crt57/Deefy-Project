<?php

namespace iutnc\deefy\action;

class DefaultAction extends Action {

    public function execute(): string {
        return "<h1>Bienvenue !</h1>";
    }
}