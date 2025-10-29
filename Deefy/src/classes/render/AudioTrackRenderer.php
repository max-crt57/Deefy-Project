<?php

namespace iutnc\deefy\render;

use iutnc\deefy\render\Renderer;

abstract class AudioTrackRenderer implements Renderer {

    const COMPACT = Renderer::COMPACT;
    const LONG = Renderer::LONG;

    public function render(int $selector): string {
        switch ($selector) {
            case self::COMPACT:
                return $this->renderCompact();
            case self::LONG:
                return $this->renderLong();
            default:
                return "<p>Mode d'affichage inconnu.</p>";
        }
    }

    abstract public function renderCompact(): string;
    abstract public function renderLong(): string;
}