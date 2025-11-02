<?php

namespace iutnc\deefy\action;

class DefaultAction extends Action {

    public function execute(): string {
        $html = "<h1>Bienvenue !</h1>";
        $html .= "<p>Gérez vos playlists, écoutez vos titres et explorez vos podcasts préférés sur DeefyApp !</p>";
        return $html;
    }
}