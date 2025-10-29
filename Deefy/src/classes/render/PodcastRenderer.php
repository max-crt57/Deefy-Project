<?php

namespace iutnc\deefy\render;

use iutnc\deefy\audio\tracks\PodcastTrack;
use iutnc\deefy\render\AudioTrackRenderer;

class PodcastRenderer extends AudioTrackRenderer {

    private PodcastTrack $podcast;

    public function __construct(PodcastTrack $podcast) {
        $this->podcast = $podcast;
    }

    public function renderCompact(): string {
        return '<div class="podcast compact">
                    <p><strong>' . $this->podcast->titre . '</strong> - ' . $this->podcast->auteur . ' (' . $this->podcast->date . ')</p>
                    <audio controls src="' . $this->podcast->nomFichierAudio . '"></audio>
                </div>';
    }

    public function renderLong(): string {
        return '<div class="podcast long">
                    <h2>' . $this->podcast->titre . '</h2>
                    <p><strong>Auteur : </strong>' . $this->podcast->auteur . '</p>
                    <p><strong>Date : </strong>' . $this->podcast->date . '</p>
                    <p><strong>Genre : </strong>' . $this->podcast->genre . '</p>
                    <p><strong>Durée : </strong>' . $this->podcast->duree . ' secondes</p>
                    <audio controls src="' . $this->podcast->nomFichierAudio . '"></audio>
                </div>';
    }
}