<?php

namespace iutnc\deefy\action;

class LogoutAction extends Action {

    public function execute(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_unset();
        session_destroy();

        return "
        <div class='text-center'>
            <p>Vous avez été déconnecté avec succès !</p>
            <a href='?action=signin-user' class='btn'>Se reconnecter</a>
        </div>";
    }
}
