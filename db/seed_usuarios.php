<?php
// ============================================================
// db/seed_usuarios.php
// Inserta los usuarios de prueba con contraseñas hasheadas.
//
// Uso:  php db/seed_usuarios.php
// ============================================================

require_once __DIR__ . '/../config/database.php';

$pdo = getConexion();

// Usuarios de prueba: [nombre, email, contraseña, rol]
$usuarios = [
    ['Administrador',  'admin@tienda.com',  'Admin123!',   'admin'],
    ['Juan García',    'juan@example.com',  'Cliente123!', 'cliente'],
    ['María López',    'maria@example.com', 'Cliente123!', 'cliente'],
];

$stmt = $pdo->prepare(
    "INSERT INTO usuarios (nombre, email, password_hash, rol)
     VALUES (:nombre, :email, :hash, :rol)
     ON CONFLICT (email) DO NOTHING"
);

foreach ($usuarios as [$nombre, $email, $password, $rol]) {
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt->execute([
        ':nombre' => $nombre,
        ':email'  => $email,
        ':hash'   => $hash,
        ':rol'    => $rol,
    ]);
    echo "  ✓ Usuario insertado: $email (contraseña: $password)\n";
}

echo "\nListo. Puedes iniciar sesión con las credenciales anteriores.\n";
