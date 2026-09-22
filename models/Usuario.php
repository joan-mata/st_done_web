<?php
// models/Usuario.php
// ============================================================
// Modelo: gestiona los datos de usuarios.
// ============================================================

require_once __DIR__ . '/../config/database.php';

class Usuario {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = getConexion();
    }

    // Obtener un usuario por email (para el login)
    public function getPorEmail(string $email): array|false {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM usuarios WHERE email = :email"
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    // Obtener un usuario por su ID
    public function getPorId(int $id): array|false {
        $stmt = $this->pdo->prepare(
            "SELECT id, nombre, email, rol, created_at FROM usuarios WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // Comprobar si un email ya está registrado
    public function existeEmail(string $email): bool {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM usuarios WHERE email = :email"
        );
        $stmt->execute([':email' => $email]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // Registrar un nuevo usuario
    public function crear(string $nombre, string $email, string $password): int {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->pdo->prepare(
            "INSERT INTO usuarios (nombre, email, password_hash)
             VALUES (:nombre, :email, :hash)
             RETURNING id"
        );
        $stmt->execute([
            ':nombre' => $nombre,
            ':email'  => $email,
            ':hash'   => $hash,
        ]);
        return (int)$stmt->fetchColumn();
    }

    // Verificar credenciales (login)
    // Devuelve el array del usuario si las credenciales son correctas, false si no.
    public function verificarCredenciales(string $email, string $password): array|false {
        $usuario = $this->getPorEmail($email);
        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            return $usuario;
        }
        return false;
    }
}
