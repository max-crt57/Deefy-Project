<?php

namespace iutnc\deefy\dispatch;

use iutnc\deefy\action\Action;
use iutnc\deefy\action\DefaultAction;
use iutnc\deefy\action\DisplayPlaylistAction;
use iutnc\deefy\action\AddPlaylistAction;
use iutnc\deefy\action\AddPodcastTrackAction;
use iutnc\deefy\action\AddUserAction;
use iutnc\deefy\action\SigninAction;
use iutnc\deefy\action\ListPlayListsAction;
use iutnc\deefy\action\DisplayCurrentPlaylistAction;
use iutnc\deefy\action\LogoutAction;

class Dispatcher {

    private string $action;

    public function __construct() {
        if (isset($_GET['action']) && $_GET['action'] !== '') {
            $this->action = $_GET['action'];
        } else {
            $this->action = 'default';
        }
    }

    public function run(): void {
        switch ($this->action) {
            case 'logout':
                $act = new LogoutAction();
                break;
            case 'current-playlist':
                $act = new DisplayCurrentPlaylistAction();
                break;
            case 'list-playlist':
                $act = new ListPlayListsAction();
                break;
            case 'display-playlist':
                $act = new DisplayPlaylistAction();
                break;
            case 'add-playlist':
                $act = new AddPlaylistAction();
                break;
            case 'add-track':
                $act = new AddPodcastTrackAction();
                break;
            case 'add-user':
                $act = new AddUserAction();
                break;
            case 'signin-user':
                $act = new SigninAction();
                break;
            default:
                $act = new DefaultAction();
                break;
        }

        $html = $act->execute();

        $this->renderPage($html);
    }

    private function renderPage(string $html): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Deefy App</title>
            <link rel="stylesheet" type="text/css" href="./src/css/index.css">
        </head>
        <body>
            <header>
                <h1>DeefyApp</h1>
                <nav>
                    <a href='?action=default'>Accueil</a>
                    <a href='?action=add-user'>Inscription</a>
                    <a href='?action=signin-user'>Connexion</a>
                    <a href='?action=list-playlist'>Mes playlists</a>
                    <a href='?action=current-playlist'>Playlist courante</a>
                    <a href='?action=add-playlist'>Créer une playlist</a>
                    <?php if (isset($_SESSION['user'])): ?>
                        <a href='?action=logout' style='color:#ef4444;font-weight:bold;'>Se déconnecter</a>
                    <?php endif; ?>
                </nav>
            </header>

            <main>
                <?= $html ?>
            </main>

            <footer>
                <p>© 2025 | CRAINCOURT Maxime | IUT Nancy-Charlemagne</p>
            </footer>
        </body>
        </html>
        <?php
    } 

}