<?php

namespace iutnc\deefy\auth;

use iutnc\deefy\exception\AuthnException;
use Exception;
use iutnc\deefy\repository\DeefyRepository;

class Authz {

    public static function checkRole(int $roleAttendu): void {
        $user = AuthnProvider::getSignedInUser();

        if ($user['role'] < $roleAttendu) {
            throw new Exception("Accès refusé : rôle non conforme !");
        }
    }

    public static function checkPlaylistOwner(int $playlistId): void {
	    $repo = DeefyRepository::getInstance();
	    $pdo = $repo->getPdo();

	    $query = $pdo->prepare("SELECT id_user FROM user2playlist WHERE id_pl = :id_pl");
	    $query->execute([':id_pl' => $playlistId]);
	    $owner = $query->fetch(\PDO::FETCH_ASSOC);

	    if (!$owner) {
	        throw new Exception("Playlist introuvable ou sans propriétaire !");
	    }

	    $user = AuthnProvider::getSignedInUser();

	    if ($owner['id_user'] != $user['id'] && $user['role'] != 100) {
	        throw new Exception("Accès refusé : vous n’êtes pas propriétaire de cette playlist !");
	    }
	}
}
