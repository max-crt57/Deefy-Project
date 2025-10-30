<?php
namespace iutnc\deefy\render;

use iutnc\deefy\audio\lists\AudioList;
use iutnc\deefy\render\Renderer; 

class AudioListRenderer implements Renderer{

	private AudioList $liste;

	public function __construct(AudioList $liste) {
        $this->liste = $liste;
    }

    public function render(int $selector = 0): string {
        $html  = "<div class='audio-list'><br>";
        $html .= "<h2>" . $this->liste->nom . "</h2><br>";
        $html .= "<ul><br>";

        foreach ($this->liste->pistes as $index => $piste) {
            $titre = $piste->titre;
            $dureeSec = $piste->duree;
            if ($dureeSec > 0){
                $duree = $dureeSec . " secondes";
            }
            else{
                $duree = "Durée inconnue";
            }

            $html .= "<li>" . ($index + 1) . ". $titre ($duree)</li><br>";
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