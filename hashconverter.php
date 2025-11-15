<?php

// ----------------------------------------------------
// ARCHIVO DE CONFIGURACIÓN TEMPORAL DE LA BASE DE DATOS
// ----------------------------------------------------
$db_config = [
    'host'      => 'localhost',
    'database'  => 'prueba123',
    'user'      => 'root',
    'password'  => '',
    'charset'   => 'utf8mb4'
];

// // ----------------------------------------------------
// // CREDENCIALES DEL NUEVO USUARIO
// // ----------------------------------------------------
// $username_to_add = 'admin'; // Cambia esto por el nombre de usuario que desees
// $password_to_add = '1234'; // Cambia esto por una contraseña fuerte
// $user_role_id = 1; // 1 = Administrador (basado en tu tabla de roles)

// // ----------------------------------------------------
// // CÓDIGO DE CONEXIÓN E INSERCIÓN
// // ----------------------------------------------------
// try {
//     // Hashea la contraseña de forma segura
//     $hashed_password = password_hash($password_to_add, PASSWORD_DEFAULT);

//     // Conexión a la base de datos usando PDO
//     $dsn = "mysql:host={$db_config['host']};dbname={$db_config['database']};charset={$db_config['charset']}";
//     $pdo = new PDO($dsn, $db_config['user'], $db_config['password']);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//     // Prepara la consulta SQL para insertar el usuario
//     $sql = "INSERT INTO priv_usuarios (usuario, contrasenia, id_rol) VALUES (:username, :password, :role)";
//     $stmt = $pdo->prepare($sql);

//     // Vincula los valores a la consulta preparada
//     $stmt->bindParam(':username', $username_to_add);
//     $stmt->bindParam(':password', $hashed_password);
//     $stmt->bindParam(':role', $user_role_id, PDO::PARAM_INT);

//     // Ejecuta la consulta
//     $stmt->execute();

//     echo "¡Usuario '{$username_to_add}' insertado correctamente en la base de datos!";

// } catch (PDOException $e) {
//     die("Error de conexión o inserción: " . $e->getMessage());
// }

// ----------------------------------------------------
// CÓDIGO DE ACTUALIZACIÓN DE CONTRASEÑA
// ----------------------------------------------------
try {
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_config['user'], $db_config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Usuario que tiene la contraseña en texto plano
    $username_to_update = 'Amogus'; 
    $plain_password = 'Amogus';

    // 1. Hashea la contraseña de forma segura
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

    // 2. Prepara la consulta para actualizar el registro del usuario
    $sql = "UPDATE priv_usuarios SET contrasenia = :password WHERE usuario = :username";
    $stmt = $pdo->prepare($sql);

    // 3. Vincula los valores y ejecuta la consulta
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':username', $username_to_update);
    $stmt->execute();

    echo "Contraseña de '{$username_to_update}' actualizada correctamente.";

} catch (PDOException $e) {
    die("Error de conexión o actualización: " . $e->getMessage());
}
?>