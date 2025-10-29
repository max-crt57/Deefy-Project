<?php

namespace iutnc\deefy\action;

use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\render\AudioListRenderer;

class AddPlaylistAction extends Action {

    public function execute(): string {
        if (empty($_SESSION)) {
            session_start();
        }

        if ($this->http_method === 'POST') {
            if (isset($_POST['name']) && $_POST['name'] !== '') {
                $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
            } 
            else {
                $name = '';
            }

            if ($name === '') {
                return "<p>Nom de playlist invalide.</p>";
            }

            $playlist = new Playlist($name);
            $_SESSION['playlist'] = $playlist;

            $renderer = new AudioListRenderer($playlist);
            $html = "<h2>Playlist créée : $name</h2>";
            $html .= $renderer->render();
            $html .= "<p><a href='?action=add-track'>Ajouter une piste</a></p>";

            return $html;
        }

        return '
        <!DOCTYPE html>
        <html lang="fr">
        <body>
            <h2>Créer une nouvelle playlist</h2>
            <form method="post" action="?action=add-playlist">
                <label for="name">Nom de la playlist :</label><br>
                <input type="text" id="name" name="name" required><br><br>
                <input type="submit" value="Créer la playlist">
            </form>
        </body>
        </html>';
    }
}
