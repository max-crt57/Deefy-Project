<?php

namespace iutnc\deefy\auth;

use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\exception\AuthnException;

class AuthnProvider {

	protected int $id;

    public static function signin(string $email, string $password): void {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = ['count' => 0, 'last_fail' => 0];
        }

        if ($_SESSION['login_attempts']['count'] >= 3) {
            $tmp_dernier_fail = time() - $_SESSION['login_attempts']['last_fail'];
            if ($tmp_dernier_fail < 300) {
                $tmp = 300 - $tmp_dernier_fail;
                throw new AuthnException("Trop de tentatives ! Réessaie dans $tmp secondes.");
            } 
            else {
                $_SESSION['login_attempts'] = ['count' => 0, 'last_fail' => 0];
            }
        }

        $pdo = DeefyRepository::getInstance()->getPdo();
        $email = filter_var(strtolower(trim($email)), FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new AuthnException("Adresse email invalide !");
        }
        $query = $pdo->prepare("SELECT * FROM user WHERE email = :email");
        $query->execute([':email' => $email]);
        $user = $query->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['passwd'])) {
            $_SESSION['login_attempts']['count']++;
            $_SESSION['login_attempts']['last_fail'] = time();
            throw new AuthnException("Email ou mot de passe incorrect");
        }

        $_SESSION['login_attempts'] = ['count' => 0, 'last_fail' => 0];

        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => htmlspecialchars($user['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            'role' => $user['role']
        ];
    }

     public static function register(string $email, string $mdp, string $mdpconfirm): void {
        $pdo = DeefyRepository::getInstance()->getPdo();
        
        $email = filter_var(strtolower(trim($email)), FILTER_SANITIZE_EMAIL);

        if (strlen($mdp) < 10) {
            throw new AuthnException("Le mot de passe doit contenir au moins 10 caractères !");
        }

        if ($mdp !== $mdpconfirm) {
            throw new AuthnException("Le mot de passe doit être le même !");
        }

        $query = $pdo->prepare("SELECT * FROM user WHERE email = :email");
        $query->execute([':email' => $email]);
        if ($query->fetch()) {
            throw new AuthnException("Un compte existe déjà avec cet email !");
        }

        $hash = password_hash($mdp, PASSWORD_DEFAULT);

        $insert = $pdo->prepare("INSERT INTO user (email, passwd, role) VALUES (:email, :mdp, 1)");
        $insert->execute([
            ':email' => $email,
            ':mdp' => $hash
        ]);
    }

    public static function getSignedInUser(): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            throw new AuthnException("Aucun utilisateur connecté");
        }

        return $_SESSION['user'];
    }

}