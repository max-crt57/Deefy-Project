<?php

namespace iutnc\deefy\action;

use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthnException;
use iutnc\deefy\repository\DeefyRepository;

class SigninAction extends Action {

    public function execute(): string {
        DeefyRepository::setConfig(__DIR__ . '/../../config/db.config.ini');

		if (empty($_SESSION)) {
            session_start();
        }

		if (isset($_SESSION['user'])) {
		    return "<p>Déjà connecté en tant que <strong>{$_SESSION['user']['email']}</strong>.</p>";
		}

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		    if (isset($_POST['email'])) {
                $email = $_POST['email'];
            } 
            else {
                $email = '';
            }

            if (isset($_POST['passwd'])) {
                $passwd = $_POST['passwd'];
            } 
            else {
                $passwd = '';
            }

		    try {
		        AuthnProvider::signin($email, $passwd);
		        return "<p>Connexion réussie ! Bienvenue, <strong>{$_SESSION['user']['email']}</strong>.</p>";
		    } 
		    catch (AuthnException $e) {
		        return "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
		    }
		} 
		else {
		    return '
			    <h2>Connexion</h2>
			    <form method="POST" action="">
			        <label for="email">Email :</label><br>
			        <input type="email" id="email" name="email" required><br><br>

			        <label for="passwd">Mot de passe :</label><br>
			        <input type="password" id="passwd" name="passwd" required><br><br>

			        <button type="submit">Se connecter</button>
			    </form>';
		}
    }
}
