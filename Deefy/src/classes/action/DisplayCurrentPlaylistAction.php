<?php

namespace iutnc\deefy\action;

use iutnc\deefy\render\AudioListRenderer;

class DisplayCurrentPlaylistAction extends Action {

    public function execute(): string {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['current_playlist'])) {
            return "<p>Aucune playlist courante n’a été sélectionnée !</p>
                    <p><a href='?action=list-playlists'>Voir mes playlists</a></p>";
        }

        $playlist = $_SESSION['current_playlist'];

        $renderer = new AudioListRenderer($playlist);
        $html = "<h2>Playlist courante : <i>" . htmlspecialchars($playlist->nom) . "</i></h2>";
        $html .= $renderer->render();

        return $html;
    }
}
