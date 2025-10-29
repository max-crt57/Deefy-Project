<?php

namespace iutnc\deefy\action;

use iutnc\deefy\audio\tracks\PodcastTrack;
use iutnc\deefy\render\AudioListRenderer;

use getID3;

class AddPodcastTrackAction extends Action {

    public function execute(): string {

        if (empty($_SESSION)) {
            session_start();
        }

        if (!isset($_SESSION['playlist'])) {
            return "<p>Créez d’abord une playlist avant d’ajouter un podcast !</p>";
        }

        if ($this->http_method === 'POST') {
            if (isset($_POST['titre']) && $_POST['titre'] !== '') {
                $titre = filter_var($_POST['titre'], FILTER_SANITIZE_STRING);
            } 
            else {
                $titre = 'Sans titre';
            }

            if (isset($_POST['auteur']) && $_POST['auteur'] !== '') {
                $auteur = filter_var($_POST['auteur'], FILTER_SANITIZE_STRING);
            } 
            else {
                $auteur = 'Inconnu';
            }

            if (isset($_POST['fichier']) && $_POST['fichier'] !== '') {
                $fichier = filter_var($_POST['fichier'], FILTER_SANITIZE_STRING);
                echo($fichier);
            } 
            else {
                $fichier = '';
            }

            $duree = 0; // valeur par défaut

            if (isset($_FILES['userfile']) && $_FILES['userfile']['error'] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['userfile']['tmp_name'];
                $file_name = $_FILES['userfile']['name'];
                $extension = strtolower(substr($file_name, -4));

                if ($extension === '.mp3' && $_FILES['userfile']['type'] === 'audio/mpeg') {
                    $new_name = uniqid('audio_') . '.mp3';
                    $destination = __DIR__ . '/../../../audio/' . $new_name;

                    if (move_uploaded_file($tmp_name, $destination)) {
                        // chemin complet pour getID3
                        $fichier = $destination;

                        $getID3 = new \getID3();
                        $fileInfo = $getID3->analyze($destination);
                        \getid3_lib::CopyTagsToComments($fileInfo);

                        if (!empty($fileInfo['playtime_seconds'])) {
                            $duree = (int)$fileInfo['playtime_seconds'];
                        } else {
                            $duree = 0;
                        }
                    } else {
                        return "<p>Erreur lors de l'upload du fichier.</p>";
                    }
                } else {
                    return "<p>Fichier invalide : seul le format MP3 est autorisé.</p>";
                }
            }

            // passer le chemin complet à PodcastTrack
            $track = new PodcastTrack($titre, $fichier, $duree);
            $_SESSION['playlist']->ajouterPiste($track);


            $renderer = new AudioListRenderer($_SESSION['playlist']);
            $html = "<h2>Podcast ajouté : <b>$titre</b></h2>";
            $html .= $renderer->render();
            $html .= "<p><a href='?action=add-track'>Ajouter encore une piste</a></p>";

            return $html;
        }

        return '
            <h2>Ajouter une piste à la playlist</h2>
            <form method="post" action="?action=add-track" enctype="multipart/form-data">
                <p>
                    <label for="titre">Titre :</label><br>
                    <input type="text" id="titre" name="titre" required>
                </p>
                <p>
                    <label for="auteur">Auteur :</label><br>
                    <input type="text" id="auteur" name="auteur">
                </p>
                <p>
                    <label for="userfile">Fichier audio (.mp3 uniquement) :</label><br>
                    <input type="file" id="userfile" name="userfile" accept=".mp3,audio/mpeg" required>
                </p>
                <p><input type="submit" value="Ajouter"></p>
            </form>';
    }
}