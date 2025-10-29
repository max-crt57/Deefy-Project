<?php

namespace iutnc\deefy\audio\tracks;

use Exception;

class PodcastTrack extends AudioTrack {
    protected string $auteur;
    protected string $date;
    protected string $genre;
    protected int $duree;

    public function __construct(string $titre, string $cheminFichierAudio, int $duree, string $genre, string $auteur, string $date) {
        parent::__construct($titre, $cheminFichierAudio, $duree);
        $this->genre = $genre;
        $this->auteur = $auteur;
        $this->date = $date;
    }

    public function __get(string $at): mixed {
        if (property_exists($this, $at)) {
            return $this->$at;
        }
        throw new Exception("invalid property : $at");
    }

    public function setAuteur(string $auteur): void {
        $this->auteur = $auteur;
    }

    public function setDate(string $date): void {
        $this->date = $date;
    }

    public function setGenre(string $genre): void {
        $this->genre = $genre;
    }

    public function getDuree(): int {
        return $this->duree;
    }
}