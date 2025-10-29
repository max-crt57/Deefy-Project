<?php

namespace iutnc\deefy\audio\lists;

use Exception;
use iutnc\deefy\audio\lists\AudioList;

class Album extends AudioList {

    private string $artiste;
    private string $dateSortie;

    public function __construct(string $nom, array $pistes) {
        if (empty($pistes)) {
            throw new Exception("Un album doit contenir au moins une piste.");
        }
        parent::__construct($nom, $pistes);
    }

    public function setArtiste(string $artiste): void {
        $this->artiste = $artiste;
    }

    public function setDateSortie(string $date): void {
        $this->dateSortie = $date;
    }
}
