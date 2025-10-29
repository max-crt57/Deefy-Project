<?php

namespace iutnc\deefy\auth;

use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\exception\AuthnException;

class AuthnProvider {

	protected int $id;

    public static function signin(string $email, string $password): void {
        $pdo = DeefyRepository::getInstance()->getPdo();
        $query = $pdo->prepare("SELECT * FROM user WHERE email = :email");
        $query->execute([':email' => $email]);
        $user = $query->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['passwd'])) {
            throw new AuthnException("Email ou mot de passe incorrect");
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
    }

     public static function register(string $email, string $mdp, string $mdpconfirm): void {
        $pdo = DeefyRepository::getInstance()->getPdo();

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