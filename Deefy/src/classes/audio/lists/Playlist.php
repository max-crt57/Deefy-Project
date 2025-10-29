<?php

namespace iutnc\deefy\audio\lists;

use iutnc\deefy\audio\tracks\AudioTrack;
use iutnc\deefy\exception\InvalidPropertyNameException;
use Exception;

class Playlist extends AudioList {

    protected int $id;

    public function __construct(string $nom, array $pistes = []) {
        parent::__construct($nom, $pistes);
    }

    public function ajouterPiste(AudioTrack $piste): void {
        if (!in_array($piste, $this->pistes, true)) { 
            $this->pistes[] = $piste;
            $this->nbPistes = count($this->pistes);
            $this->dureeTotale = 0;
            foreach ($this->pistes as $p) {
                $this->dureeTotale += $p->getDuree();
            }
        }
    }

    public function supprimerPiste(int $indice): void {
        if (isset($this->pistes[$indice])) {
            array_splice($this->pistes, $indice, 1);
            $this->nbPistes = count($this->pistes);
            $this->dureeTotale = 0;
            foreach ($this->pistes as $p) {
                $this->dureeTotale += $p->getDuree();
            }
        } else {
            throw new Exception("Indice de piste invalide : $indice");
        }
    }

    public function ajouterListePistes(array $nouvellesPistes): void {
        foreach ($nouvellesPistes as $piste) {
            if ($piste instanceof AudioTrack && !in_array($piste, $this->pistes, true)) {
                $this->pistes[] = $piste;
            }
        }
        $this->nbPistes = count($this->pistes);
        $this->dureeTotale = 0;
        foreach ($this->pistes as $p) {
            $this->dureeTotale += $p->getDuree();
        }
    }

    public function __get(string $at): mixed {
        if (property_exists($this, $at)) {
            return $this->$at;
        }
        throw new InvalidPropertyNameException($at);
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }
}