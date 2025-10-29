<?php

namespace iutnc\deefy\audio\lists;

use iutnc\deefy\exception\InvalidPropertyNameException;

abstract class AudioList{

	protected string $nom;
	protected int $nbPistes;
	protected int $dureeTotale;
	protected array $pistes = [];

	public function __construct(string $nom, array $pistes = []) {
        $this->nom = $nom;
        $this->pistes = $pistes;
        $this->nbPistes = count($this->pistes);
        $this->dureeTotale = 0;
        foreach ($this->pistes as $piste) {
            if (isset($piste->duree)) {
			    $this->dureeTotale += $piste->duree;
			} 
			else {
			    $this->dureeTotale += 0;
			}
        }
    }

    public function __get(string $at): mixed {
        if (property_exists($this, $at)) {
            return $this->$at;
        }
        throw new InvalidPropertyNameException($at);
    }
}