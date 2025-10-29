<?php

namespace iutnc\deefy\repository;

use PDO;
use Exception;
use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\audio\tracks\AudioTrack;
use iutnc\deefy\audio\tracks\AlbumTrack;
use iutnc\deefy\audio\tracks\PodcastTrack;

class DeefyRepository{

    private PDO $pdo;
    private static ?DeefyRepository $instance = null;
    private static array $config = [];

    private function __construct(array $conf){
        $this->pdo = new PDO($conf['dsn'], $conf['user'], $conf['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public static function setConfig(string $file): void{
        $conf = parse_ini_file($file);
        if ($conf === false) {
            throw new Exception("Erreur de lecture du fichier de configuration");
        }

        self::$config = ['dsn'  => $conf['dsn'],
                         'user' => $conf['username'],
                         'pass' => $conf['password']];
    }

    public static function getInstance(): DeefyRepository{
        if (is_null(self::$instance)) {
            if (empty(self::$config)) {
                throw new Exception("Configuration non définie : appelez setConfig() avant getInstance()");
            }
            self::$instance = new DeefyRepository(self::$config);
        }
        return self::$instance;
    }

    public function findAllPlaylists(): array {
        $query = $this->pdo->query("SELECT id, nom FROM playlist");
        $playlists = [];
        while ($ligne = $query->fetch(PDO::FETCH_ASSOC)) {
            $playlist = new Playlist($ligne['nom']);
            $playlist->setId($ligne['id']);
            $playlists[] = $playlist;
        }
        return $playlists;
    }

    public function saveEmptyPlaylist(Playlist $p): void {
        $nom = $p->__get('nom');
        $query = $this->pdo->prepare("INSERT INTO playlist (nom) VALUES (:nom)");
        $query->execute([':nom' => $nom]);
        $p->setId((int)$this->pdo->lastInsertId());
    }

    public function savePodcastTrack(AudioTrack $t): void {
        $queryID = $this->pdo->query("SELECT MAX(id) AS max_id FROM track");
        $row = $queryID->fetch(\PDO::FETCH_ASSOC);
        if (is_null($row['max_id'])){
            $newId = 1;
        }
        else{
            $newId = $row['max_id'] + 1;
        }

        $t->setId($newId);
        $query = $this->pdo->prepare("
            INSERT INTO track (id, titre, genre, duree, filename)
            VALUES (:id, :titre, :genre, :duree, :filename)
        ");

        $query->execute([
            ':id'       => $t->__get('id'),
            ':titre'    => $t->__get('titre'),
            ':genre'    => $t->__get('genre'),
            ':duree'    => $t->__get('duree'),
            ':filename' => $t->__get('nomFichierAudio')
        ]);
    }


    public function addTrackToPlaylist(int $idPlaylist, int $idTrack): void {
        // Récupérer la position actuelle max dans la playlist
        $query1 = $this->pdo->prepare("SELECT MAX(no_piste_dans_liste) AS max_pos FROM playlist2track WHERE id_pl = :id_pl");
        $query1->execute([':id_pl' => $idPlaylist]);
        $maxPos = $query1->fetch(PDO::FETCH_ASSOC)['max_pos'];

        // Si la playlist est vide, maxPos sera null : on commence à 1
        if (is_null($maxPos)){
            $newPos = 1;
        }
        else{
            $newPos = $maxPos + 1;
        }

        // Insérer la piste avec sa position
        $query2 = $this->pdo->prepare("INSERT INTO playlist2track(id_pl, id_track, no_piste_dans_liste) VALUES (:id_pl, :id_track, :pos)");
        $query2->execute([':id_pl'    => $idPlaylist, ':id_track' => $idTrack, ':pos'      => $newPos]);
    }

    public function getPdo():PDO{
        return $this->pdo;
    }

    public function findPlaylistById(int $id): Playlist {
        $query = $this->pdo->prepare("SELECT * FROM playlist WHERE id = :id");
        $query->execute([':id' => $id]);
        $data = $query->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $playlist = new Playlist($data['nom']);
        $playlist->setId((int)$data['id']);

        $query2 = $this->pdo->prepare("SELECT track.* 
                                       FROM track
                                       INNER JOIN playlist2track ON track.id = playlist2track.id_track
                                       WHERE playlist2track.id_pl = :id
                                       ORDER BY playlist2track.no_piste_dans_liste ASC");
        $query2->execute([':id' => $id]);
        $tracks = [];

        while ($ligne = $query2->fetch(PDO::FETCH_ASSOC)) {
            $auteur = "Inconnu";
            if (isset($ligne['auteur']) && $ligne['auteur'] !== '') {
                $auteur = $ligne['auteur'];
            }

            $date = "Inconnue";
            if (isset($ligne['date']) && $ligne['date'] !== '') {
                $date = $ligne['date'];
            }
            $track = new \iutnc\deefy\audio\tracks\PodcastTrack(
                $ligne['titre'],
                $ligne['filename'],
                (int)$ligne['duree'],
                $ligne['genre'],
                $auteur,
                $date
            );
            $track->setId((int)$ligne['id']);
            $tracks[] = $track;
        }

        $playlist->ajouterListePistes($tracks);
        return $playlist;
    }

    public function getPlaylistsByUser(int $user_id): array {
        $query = "SELECT p.* FROM playlist p INNER JOIN user2playlist u2p ON p.id = u2p.id_pl WHERE u2p.id_user = ?";
        $sql = $this->pdo->prepare($query);
        $sql->execute([$user_id]);
        $lignes = $sql->fetchAll();

        $playlists = [];
        foreach ($lignes as $ligne) {
            $playlist = new \iutnc\deefy\audio\lists\Playlist($ligne['nom']);
            $playlist->setId((int)$ligne['id']);
            $playlists[] = $playlist;
        }
        return $playlists;
    }

    public function saveTrackToPlaylist($track, $playlist): void {
        $stmt = $this->pdo->prepare("INSERT INTO track (titre, filename, duree, genre, type) VALUES (:titre, :filename, :duree, :genre, :type)");
        $titre = $track->titre;
        $filename = $track->nomFichierAudio;
        $duree = $track->duree;
        $genre = $track->genre;

        if ($track instanceof AlbumTrack) {
            $type = 'A';
        } 
        else {
            $type = 'P';
        }

        $stmt = $this->pdo->prepare("INSERT INTO track (titre, filename, duree, genre, type) VALUES (:titre, :filename, :duree, :genre, :type)");
        $stmt->execute([
            ':titre' => $titre,
            ':filename' => $filename,
            ':duree' => $duree,
            ':genre' => $genre,
            ':type' => $type
        ]);

        $track_id = $this->pdo->lastInsertId();

        if ($track instanceof AlbumTrack) {
            $stmt2 = $this->pdo->prepare("UPDATE track SET artiste_album = :artiste, titre_album = :album, annee_album = :annee, numero_album = :numero WHERE id = :id");
            $stmt2->execute([
                ':artiste' => $track->artiste,
                ':album' => $track->album,
                ':annee' => $track->annee,
                ':numero' => $track->numero,
                ':id' => $track_id
            ]);
        } 
        else {
            if ($track instanceof PodcastTrack) {
                $stmt3 = $this->pdo->prepare("UPDATE track SET auteur_podcast = :auteur, date_posdcast = :date WHERE id = :id");
                $stmt3->execute([
                    ':auteur' => $track->auteur,
                    ':date' => $track->date,
                    ':id' => $track_id
                ]);
            }
        }

        $stmt4 = $this->pdo->prepare("INSERT INTO playlist2track (id_pl, id_track, no_piste_dans_liste) VALUES (:id_pl, :id_track, :no_piste)");

        if (isset($playlist->liste_pistes)) {
            $numero = count($playlist->liste_pistes);
        } 
        else {
            $numero = 0;
        }

        $stmt4->execute([
            ':id_pl' => $playlist->id,
            ':id_track' => $track_id,
            ':no_piste' => $numero + 1
        ]);
    }

}