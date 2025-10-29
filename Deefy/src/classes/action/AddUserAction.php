<?php

namespace iutnc\deefy\action;

use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\exception\AuthnException;

class AddUserAction extends Action {

    public function execute(): string {
        DeefyRepository::setConfig(__DIR__ . '/../../config/db.config.ini');

        if (empty($_SESSION)) {
            session_start();
        }

        if ($this->http_method === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

            if (isset($_POST['mdp'])) {
                $mdp = $_POST['mdp'];
            } 
            else {
                $mdp = '';
            }

            if (isset($_POST['mdpconfirm'])) {
                $mdpconfirm = $_POST['mdpconfirm'];
            } 
            else {
                $mdpconfirm = '';
            }

            if ($mdp !== $mdpconfirm) {
                return "<p style='color:red;'>Le mot de passe doit être le même !</p>";
            }

            try {
                AuthnProvider::register($email, $mdp, $mdpconfirm);
                return "<p>Inscription réussie ! Vous pouvez maintenant vous connecter !</p>";
            } 
            catch (AuthnException $e) {
                return "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
            }
        }

        else {
            if (isset($_SESSION['user'])) {
                return "<p>Vous êtes déjà connecté en tant que <strong>{$_SESSION['user']['email']}</strong>.</p>";
            }
        }

        return "
        <h2>Inscription</h2>
        <form method='post' action='?action=add-user'>
            <p>
                <label for='email'>Email :</label><br>
                <input type='email' id='email' name='email' required>
            </p>
            <p>
                <label for='mdp'>Mot de passe :</label><br>
                <input type='password' id='mdp' name='mdp' required>
            </p>
            <p>
                <label for='mdpconfirm'>Confirmez le mot de passe :</label><br>
                <input type='password' id='mdpconfirm' name='mdpconfirm' required>
            </p>
            <p>
                <input type='submit' value='S’inscrire'>
            </p>
        </form>";

    }
}