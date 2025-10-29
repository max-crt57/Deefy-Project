<?php

namespace iutnc\deefy\audio\tracks;

use iutnc\deefy\exception\InvalidPropertyNameException;
use iutnc\deefy\exception\InvalidPropertyValueException;

abstract class AudioTrack {
    protected string $titre;
    protected string $nomFichierAudio;
    protected string $genre;
    protected int $duree;
    protected int $id;

    public function __construct(string $titre, string $cheminFichierAudio, int $duree) {
        $this->titre = $titre;
        $this->nomFichierAudio = $cheminFichierAudio;
        $this->duree = $duree;
    }

    public function __get(string $at): mixed {
        if (property_exists($this, $at)) {
            return $this->$at;
        }
        throw new InvalidPropertyNameException($at);
    }

    public function setGenre(String $genre):void{
        $this->genre = $genre;
    }

    public function setDuree(int $duree): void {
        if ($duree < 0) {
            throw new InvalidPropertyValueException("duree", $duree);
        }
        else{
            $this->duree = $duree;
        }
    }

    public function getId():int{
        return $this->id;
    }

    public function setId(int $id):void{
        $this->id = $id;
    }
}