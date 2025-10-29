<?php

namespace iutnc\deefy\audio\tracks;

use Exception;

class PodcastTrack extends AudioTrack {
    protected string $auteur;
    protected string $date;
    protected int $duree;

    public function __construct(string $titre, string $cheminFichierAudio, int $duree) {
        parent::__construct($titre, $cheminFichierAudio, $duree);
    }

    public function __get(string $at):mixed {
        if (property_exists ($this, $at)){
            return $this->$at;
        }
        throw new Exception ("invalid property : $at");
    }

    public function setAuteur(string $auteur):void{
        $this->auteur = $auteur;
    }

    public function setDate(string $date):void{
        $this->date = $date;
    }

    public function getDuree(): int {
        return $this->duree;
    }

}