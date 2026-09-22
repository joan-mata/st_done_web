<?php
// models/Usuario.php
// ============================================================
// Modelo: gestiona los datos de los usuarios registrados.
// Sin HTML. Solo lógica de acceso a datos con PDO preparado.
// ============================================================

require_once __DIR__ . '/../config/database.php';

class Usuario {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = getConexion();
    }

    // -------------------------------------------------------
    // Obtener un usuario completo por su email.
    // Se usa, por ejemplo, en el proceso de login.
    // Devuelve el array de la fila o false si no existe.
    // -------------------------------------------------------
    public function getPorEmail(string $email): array|false {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM usuarios WHERE email = :email"
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    // -------------------------------------------------------
    // Obtener un usuario por su ID.
    // Devuelve todos los campos, incluidos los de perfil
    // (telefono, direccion, ciudad, codigo_postal).
    // Devuelve false si no se encuentra.
    // -------------------------------------------------------
    public function getPorId(int $id): array|false {
        $stmt = $this->pdo->prepare(
            "SELECT id, nombre, email, rol,
                    telefono, direccion, ciudad, codigo_postal,
                    created_at
             FROM usuarios
             WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // -------------------------------------------------------
    // Comprobar si un email ya está registrado en la base
    // de datos. Devuelve true si existe, false si no.
    // -------------------------------------------------------
    public function existeEmail(string $email): bool {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM usuarios WHERE email = :email"
        );
        $stmt->execute([':email' => $email]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // -------------------------------------------------------
    // Registrar un nuevo usuario.
    // La contraseña se hashea con bcrypt a coste 12.
    // Devuelve el ID del nuevo registro.
    // -------------------------------------------------------
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

    // -------------------------------------------------------
    // Verificar las credenciales de un usuario en el login.
    // Primero busca el email y luego comprueba la contraseña
    // con password_verify contra el hash almacenado.
    // Devuelve el array del usuario si son correctas, o false.
    // -------------------------------------------------------
    public function verificarCredenciales(string $email, string $password): array|false {
        $usuario = $this->getPorEmail($email);
        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            return $usuario;
        }
        return false;
    }

    // -------------------------------------------------------
    // Actualizar los datos de perfil de un usuario.
    // Antes de actualizar comprueba que el email no esté
    // en uso por otro usuario distinto (unicidad excluyendo
    // al propio usuario).
    // Devuelve true si la actualización fue correcta, false
    // si el email ya pertenece a otra cuenta.
    // -------------------------------------------------------
    public function actualizar(
        int    $id,
        string $nombre,
        string $email,
        string $telefono,
        string $direccion,
        string $ciudad,
        string $codigoPostal
    ): bool {
        // Comprobar si el email ya lo usa otro usuario
        $stmtCheck = $this->pdo->prepare(
            "SELECT COUNT(*) FROM usuarios
             WHERE email = :email AND id != :id"
        );
        $stmtCheck->execute([':email' => $email, ':id' => $id]);
        if ((int)$stmtCheck->fetchColumn() > 0) {
            // El email ya está en uso por otra cuenta
            return false;
        }

        // Actualizar los datos del perfil
        $stmt = $this->pdo->prepare(
            "UPDATE usuarios
             SET nombre       = :nombre,
                 email        = :email,
                 telefono     = :telefono,
                 direccion    = :direccion,
                 ciudad       = :ciudad,
                 codigo_postal = :codigo_postal
             WHERE id = :id"
        );
        $stmt->execute([
            ':nombre'        => $nombre,
            ':email'         => $email,
            ':telefono'      => $telefono,
            ':direccion'     => $direccion,
            ':ciudad'        => $ciudad,
            ':codigo_postal' => $codigoPostal,
            ':id'            => $id,
        ]);
        return true;
    }
}
