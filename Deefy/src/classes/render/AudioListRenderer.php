<?php
namespace iutnc\deefy\render;

use iutnc\deefy\audio\lists\AudioList;
use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\render\Renderer;
use PDO;

class AudioListRenderer implements Renderer{

	private AudioList $liste;

	public function __construct(AudioList $liste) {
        $this->liste = $liste;
    }

    public function render(int $selector = 0): string {

        DeefyRepository::setConfig(__DIR__ . '/../../config/db.config.ini');
        $repo = DeefyRepository::getInstance();
        $pdo = $repo->getPdo();

        $stmt = $pdo->prepare("
            SELECT t.titre, t.duree, t.filename 
            FROM track t
            INNER JOIN playlist2track p2t ON t.id = p2t.id_track
            WHERE p2t.id_pl = :id
            ORDER BY p2t.no_piste_dans_liste");
        $stmt->execute([':id' => $this->liste->id]);
        $tracks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $html  = "<div class='audio-list'><br>";
        $html .= "<h2>" . htmlspecialchars($this->liste->nom) . "</h2><br>";

        if (empty($tracks)) {
            $html .= "<p>Aucune piste dans cette playlist.</p>";
        } 
        else {
            $html .= "<ul><br>";
            foreach ($tracks as $index => $track) {
                $titre = htmlspecialchars($track['titre']);
                $duree = $track['duree'] > 0 ? $track['duree'] . " secondes" : "Durée inconnue";
                $filename = htmlspecialchars($track['filename']);
                $src = "audio/" . $filename;

                $html .= "<li>" . ($index + 1) . ". $titre ($duree)</li><br>";
                $html .= "<audio controls src='$src' style='width:300px;'></audio><br><br>";
            }
            $html .= "</ul><br>";
        }
        $html .= "</ul><br>";
        $html .= "<p><strong>Nombre de pistes :</strong> " . $this->liste->nbPistes . "</p><br>";
        $html .= "<p><strong>Durée totale :</strong> " . $this->liste->dureeTotale . " secondes</p><br>";
        $html .= "<p><a href='?action=add-track'>Ajouter une nouvelle piste audio ou un podcast</a></p><br>";
        $html .= "<p><a href='?action=list-playlist'>Retour à mes playlists</a></p><br>";
        $html .= "</div><br>";

        return $html;
    }
    
}