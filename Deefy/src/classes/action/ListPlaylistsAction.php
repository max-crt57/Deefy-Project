<?php
namespace iutnc\deefy\action;

use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthnException;

class ListPlaylistsAction extends Action {

    public function execute(): string {

        DeefyRepository::setConfig(__DIR__ . '/../../config/db.config.ini');

        try{
            $user = AuthnProvider::getSignedInUser();
        }
        catch(AuthnException $e){
            return "<p style='color:red;'>Veuillez vous connecter pour voir vos playlists !</p>";
        }

        if ($user === null) {
            return "<p>Vous devez être connecté pour accéder à vos playlists !</p>";
        }

        $repo = DeefyRepository::getInstance();
        if (isset($user['role']) && (int)$user['role'] === 100) {
            $stmt = $repo->getPdo()->prepare("SELECT * FROM playlist");
            $stmt->execute();
            $playlists = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } 
        else {
            $playlists = $repo->getPlaylistsByUser((int) $user['id']);
        }

        if (empty($playlists)) {
            return "<p>Vous n’avez encore aucune playlist ! <a href='?action=add-playlist'>Créer une playlist</a></p>";
        }

        $html = "<h2>Mes Playlists</h2><ul>";
        foreach ($playlists as $pl) {
            if (is_object($pl)) {
                $id = $pl->getId();
                $nom = $pl->nom;
            } 
            else {
                $id = $pl['id'];
                $nom = $pl['nom'];
            }
            $html .= "<li><a href='?action=display-playlist&id={$id}'>"
                  . htmlspecialchars($nom, ENT_HTML5)
                  . "</a></li>";
        }
        $html .= "</ul>";

        return $html;
    }
}
