<?php

namespace iutnc\deefy\action;

use iutnc\deefy\audio\tracks\PodcastTrack;
use iutnc\deefy\audio\tracks\AlbumTrack;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\repository\DeefyRepository;
use getID3;

class AddPodcastTrackAction extends Action {

    public function execute(): string {

        DeefyRepository::setConfig(__DIR__ . '/../../config/db.config.ini');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['current_playlist'])) {
            return "<p>Aucune playlist courante ! Sélectionnez d’abord une playlist avant d’ajouter une piste !</p>";
        }

        $playlist = $_SESSION['current_playlist'];

        if ($this->http_method === 'POST') {

            if (!isset($_POST['type'])) {
                return "
                <h2>Choisir le type de piste</h2>
                <form method='post' action='?action=add-track'>
                    <p>
                        <input type='radio' id='audio' name='type' value='A' checked>
                        <label for='audio'>Audio</label>
                        <input type='radio' id='podcast' name='type' value='P'>
                        <label for='podcast'>Podcast</label>
                    </p>
                    <p><input type='submit' value='Continuer'></p>
                </form>";
            }

            if ($_POST['type'] === 'A' && !isset($_POST['titre'])) {
                return "
                <h2>Ajouter un audio</h2>
                <form method='post' action='?action=add-track' enctype='multipart/form-data'>
                    <input type='hidden' name='type' value='A'>
                    <p><label for='titre'>Titre :</label><br><input type='text' id='titre' name='titre' required></p>
                    <p><label for='genre'>Genre :</label><br><input type='text' id='genre' name='genre' required></p>
                    <p><label for='artiste'>Artiste :</label><br><input type='text' id='artiste' name='artiste' required></p>
                    <p><label for='album'>Album :</label><br><input type='text' id='album' name='album' required></p>
                    <p><label for='annee'>Année :</label><br><input type='number' id='annee' name='annee' min='1900' max='2100' required></p>
                    <p><label for='userfile'>Fichier (.mp3) :</label><br><input type='file' id='userfile' name='userfile' accept='.mp3,audio/mpeg' required></p>
                    <p><input type='submit' value='Ajouter'></p>
                </form>";
            }

            if ($_POST['type'] === 'P' && !isset($_POST['titre'])) {
                return "
                <h2>Ajouter un podcast</h2>
                <form method='post' action='?action=add-track' enctype='multipart/form-data'>
                    <input type='hidden' name='type' value='P'>
                    <p><label for='titre'>Titre :</label><br><input type='text' id='titre' name='titre' required></p>
                    <p><label for='genre'>Genre :</label><br><input type='text' id='genre' name='genre'></p>
                    <p><label for='artiste'>Auteur :</label><br><input type='text' id='artiste' name='artiste'></p>
                    <p><label for='date'>Date :</label><br><input type='date' id='date' name='date'></p>
                    <p><label for='userfile'>Fichier (.mp3) :</label><br><input type='file' id='userfile' name='userfile' accept='.mp3,audio/mpeg' required></p>
                    <p><input type='submit' value='Ajouter'></p>
                </form>";
            }

            if (isset($_POST['titre']) && $_POST['titre'] !== '') {
                $titre = filter_var($_POST['titre'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            } 
            else {
                $titre = 'Sans titre';
            }
            if (isset($_POST['genre']) && $_POST['genre'] !== '') {
                $genre = filter_var($_POST['genre'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            } 
            else {
                $genre = 'Inconnu';
            }

            $type = $_POST['type'];
            $duree = 0;
            $filename = '';

            if (isset($_FILES['userfile']) && $_FILES['userfile']['error'] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['userfile']['tmp_name'];
                $file_name = $_FILES['userfile']['name'];
                $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                if ($extension === 'mp3' && $_FILES['userfile']['type'] === 'audio/mpeg') {
                    $new_name = uniqid('audio_') . '.mp3';
                    $destination = __DIR__ . '/../../../audio/' . $new_name;
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $filename = $new_name;
                        $getID3 = new \getID3();
                        $fileInfo = $getID3->analyze($destination);
                        \getid3_lib::CopyTagsToComments($fileInfo);
                        if (!empty($fileInfo['playtime_seconds'])) $duree = (int)$fileInfo['playtime_seconds'];
                    } 
                    else{
                        return "<p>Erreur lors de l’upload du fichier !</p>";
                    }
                } 
                else{
                    return "<p>Fichier invalide !</p>";
                }
            }
            else {
                return "<p>Aucun fichier uploadé !</p>";
            }

            $repo = DeefyRepository::getInstance();
            $playlistId = $playlist->id;

            $stmtCheck = $repo->getPDO()->prepare("
                SELECT COUNT(*) 
                FROM track 
                INNER JOIN playlist2track ON track.id = playlist2track.id_track
                WHERE playlist2track.id_pl = :id_pl
                AND (track.titre = :titre OR track.filename = :filename)
            ");
            $stmtCheck->execute([
                ':id_pl' => $playlistId,
                ':titre' => $titre,
                ':filename' => $filename
            ]);

            $exists = $stmtCheck->fetchColumn();

            if ($exists > 0) {
                return "<p style='color:red;'>Cette piste existe déjà dans la playlist <b>{$playlist->nom}</b> !</p>";
            }


            if ($type === 'A') {
                if (isset($_POST['artiste']) && $_POST['artiste'] !== '') {
                    $auteur = filter_var($_POST['artiste'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                } 
                else {
                    $auteur = 'Inconnu';
                }
                if (isset($_POST['album']) && $_POST['album'] !== '') {
                    $album = filter_var($_POST['album'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                } 
                else {
                    $album = 'Album sans titre';
                }
                if (isset($playlist->liste_pistes)) {
                    $numero = count($playlist->liste_pistes) + 1;
                } 
                else {
                    $numero = 1;
                }
                if (isset($_POST['annee']) && $_POST['annee'] !== '') {
                    $annee = (int)$_POST['annee'];
                } 
                else {
                    $annee = (int)date('Y');
                }

            $track = new AlbumTrack($titre, $filename, $duree, $numero, $genre, $auteur, $album, $annee);
            } 
            else {
                if (isset($_POST['artiste']) && $_POST['artiste'] !== '') {
                    $auteur = filter_var($_POST['artiste'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                } 
                else {
                    $auteur = 'Inconnu';
                }
                if (isset($_POST['date']) && $_POST['date'] !== '') {
                    $date = $_POST['date'];
                } 
                else {
                    $date = date('Y-m-d');
                }
                $track = new PodcastTrack($titre, $filename, $duree, $genre, $auteur, $date);
            }

            $playlist->ajouterPiste($track);
            $repo = DeefyRepository::getInstance();
            $repo->saveTrackToPlaylist($track, $playlist);
            $renderer = new AudioListRenderer($playlist);
            $html = "<h2>Piste ajoutée à la playlist <i>{$playlist->nom}</i></h2>";
            $html .= $renderer->render();
            return $html;
        }

        return "
        <h2>Choisir le type de piste</h2>
        <form method='post' action='?action=add-track'>
            <p>
                <input type='radio' id='audio' name='type' value='A' checked>
                <label for='audio'>Audio</label>
                <input type='radio' id='podcast' name='type' value='P'>
                <label for='podcast'>Podcast</label>
            </p>
            <p><input type='submit' value='Continuer'></p>
        </form>";
    }
}