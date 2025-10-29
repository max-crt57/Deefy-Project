<?php

namespace iutnc\deefy\audio\tracks;

use Exception;
use iutnc\deefy\exception\InvalidPropertyNameException;

class AlbumTrack extends AudioTrack {

    protected string $genre;
    protected string $artiste;
    protected string $album;
    protected int $annee;
    protected int $numero;

    public function __construct(string $titre, string $cheminFichierAudio, int $duree, int $numero, string $genre, ?string $artiste = null, ?string $album = null, ?int $annee = null) {
        parent::__construct($titre, $cheminFichierAudio, $duree);
        $this->numero = $numero;
        $this->genre = $genre;

        if ($artiste !== null) {
            $this->artiste = $artiste;
        } else {
            $this->artiste = 'Inconnu';
        }

        if ($album !== null) {
            $this->album = $album;
        } else {
            $this->album = 'Album sans titre';
        }

        if ($annee !== null) {
            $this->annee = $annee;
        } else {
            $this->annee = (int)date('Y');
        }
    }

    public function __get(string $at): mixed {
        if (property_exists($this, $at)) {
            return $this->$at;
        }
        throw new InvalidPropertyNameException("Invalid property: $at");
    }

    public function setArtiste(string $artiste): void { 
        $this->artiste = $artiste; 
    }
    public function setAlbum(string $album): void { 
        $this->album = $album; 
    }
    public function setAnnee(int $annee): void { 
        $this->annee = $annee; 
    }
    public function setGenre(string $genre): void { 
        $this->genre = $genre; 
    }
}
