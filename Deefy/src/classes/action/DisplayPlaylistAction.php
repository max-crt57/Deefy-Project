<?php

namespace iutnc\deefy\action;

use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\auth\Authz;
use iutnc\deefy\exception\AuthnException;
use Exception;

class DisplayPlaylistAction extends Action {

    public function execute(): string {
        DeefyRepository::setConfig(__DIR__ . '/../../config/db.config.ini');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_GET['id'])) {
            return "<p>Aucune playlist spécifiée !</p>";
        }

        $id = (int) $_GET['id'];

        try {
            Authz::checkPlaylistOwner($id);

            $repo = DeefyRepository::getInstance();
            $playlist = $repo->findPlaylistById($id);

            if ($playlist === null) {
                return "<p>Playlist introuvable !</p>";
            }

            $_SESSION['current_playlist'] = $playlist;

            $renderer = new AudioListRenderer($playlist);
            return $renderer->render(1);
        } 
        catch (AuthnException | Exception $e) {
            return "<p style='color:red;'>" . $e->getMessage() . "</p>";
        }
    }
}
