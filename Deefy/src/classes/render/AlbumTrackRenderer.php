<?php

namespace iutnc\deefy\render;

use iutnc\deefy\audio\tracks\AlbumTrack;
use iutnc\deefy\render\AudioTrackRenderer;

class AlbumTrackRenderer extends AudioTrackRenderer{

    private AlbumTrack $piste;

    public function __construct(AlbumTrack $piste) {
        $this->piste = $piste;
    }

    public function renderCompact(): string {
        return '<div class="track compact">
                    <p><strong>' . $this->piste->titre . '</strong> - ' . $this->piste->artiste . ' (' . $this->piste->annee . ')</p>
                    <audio controls src="' . $this->piste->nomFichierAudio . '"></audio>
                </div>';
    }

    public function renderLong(): string {
        return '<div class="track long">
                    <h2>' . $this->piste->titre . '</h2>
                    <p><strong>Artiste : </strong>' . $this->piste->artiste . '</p>
                    <p><strong>Album : </strong>' . $this->piste->album . '</p>
                    <p><strong>Année : </strong>' . $this->piste->annee . '</p>
                    <p><strong>Numéro de piste : </strong>' . $this->piste->numero . '</p>
                    <p><strong>Genre : </strong>' . $this->piste->genre . '</p>
                    <p><strong>Durée : </strong>' . $this->piste->duree . ' secondes</p>
                    <audio controls src="' . $this->piste->nomFichierAudio . '"></audio>
                </div>';
    }
}