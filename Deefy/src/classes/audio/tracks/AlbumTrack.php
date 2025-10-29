<?php

namespace iutnc\deefy\audio\tracks;
use Exception;
use iutnc\deefy\exception\InvalidPropertyNameException;

class AlbumTrack extends AudioTrack {
    private string $artiste;
    private string $album;
    private int $annee;
    private int $numero;

    public function __construct(string $titre, string $cheminFichierAudio, string $album, int $numero) {
        parent::__construct($titre, $cheminFichierAudio);
        $this->album = $album;
        $this->numero = $numero;
    }

    public function __get(string $at):mixed {
        if (property_exists ($this, $at)){
            return $this->$at;
        }
        throw new Exception ("invalid property : $at");
    }

    public function setArtiste(string $artiste):void{
        $this->artiste = $artiste;
    }

    public function setAnnee(int $annee):void{
        $this->annee = $annee;
    }
}