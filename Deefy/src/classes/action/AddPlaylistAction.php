<?php

namespace iutnc\deefy\action;

use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthnException;
use iutnc\deefy\audio\lists\Playlist;

class AddPlaylistAction extends Action {

    public function execute(): string {

        DeefyRepository::setConfig(__DIR__ . '/../../config/db.config.ini');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $user = AuthnProvider::getSignedInUser();
        } 
        catch (AuthnException $e) {
            return "<p style='color:red;'>Veuillez vous connecter pour créer une playlist !</p>";
        }

        $user_id = null;

        if (is_object($user) && property_exists($user, 'id')) {
            $user_id = (int) $user->id;
        } 
        elseif (is_array($user) && isset($user['id'])) {
            $user_id = (int) $user['id'];
        } 
        elseif (isset($_SESSION['user'])) {
            $sess_user = $_SESSION['user'];
            if (is_object($sess_user) && property_exists($sess_user, 'id')) {
                $user_id = (int) $sess_user->id;
            } 
            elseif (is_array($sess_user) && isset($sess_user['id'])) {
                $user_id = (int) $sess_user['id'];
            }
        }

        if ($user_id === null) {
            return "<p style='color:red;'>Erreur : aucun utilisateur identifié !</p>";
        }

        if ($this->http_method === 'POST') {
            if (isset($_POST['name']) && trim($_POST['name']) !== '') {
                $name = filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS);
            } else {
                return "<p style='color:red;'>Nom de playlist invalide !</p>";
            }

            $playlist = new Playlist($name);

            $repo = DeefyRepository::getInstance();
            try {
                $repo->saveEmptyPlaylist($playlist, $user_id);
            } catch (\Exception $e) {
                return "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
            }

            $_SESSION['current_playlist'] = $playlist;

            return "<h2>Playlist créée : " . htmlspecialchars($name) . "</h2>
                    <p><a href='?action=add-track'>Ajouter une piste</a></p>";
        }

        return '
        <h2>Créer une nouvelle playlist</h2>
        <form method="post" action="?action=add-playlist">
            <label for="name">Nom de la playlist :</label><br>
            <input type="text" id="name" name="name" required><br><br>
            <input type="submit" value="Créer la playlist">
        </form>';
    }
}