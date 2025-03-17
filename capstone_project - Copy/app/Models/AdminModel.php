<?php
require_once __DIR__ . '/../Helpers/database.php';

class AdminModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ✅ CREATE USER
    public function createUser($firstName, $lastName, $email, $password, $role, $address, $phone, $gender) {
        if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($role) || empty($address) || empty($phone) || empty($gender)) {
            return false; // Return false if any field is missing
        }

        // ✅ Check if email exists
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return false; // Email already exists
        }

        // ✅ Insert new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $status = 'active';

        $sql = "INSERT INTO users (first_name, last_name, email, password, role, status, gender, address, phone) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssss", $firstName, $lastName, $email, $hashedPassword, $role, $status, $gender, $address, $phone);

        return $stmt->execute();
    }

    // ✅ GET USER BY ID
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // ✅ UPDATE USER
    public function updateUser($id, $firstName, $lastName, $email, $role, $address, $phone, $gender) {
        $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, address = ?, phone = ?, gender = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssi", $firstName, $lastName, $email, $role, $address, $phone, $gender, $id);
        return $stmt->execute();
    }

    // ✅ DELETE & ARCHIVE USER
    public function archiveAndDeleteUser($id) {
        $user = $this->getUserById($id);
        if (!$user) return false; // If user doesn't exist, return false

        // ✅ Move user to archive first
        $archiveSql = "INSERT INTO archives (user_id, first_name, last_name, email, role, gender, address, phone, archived_at) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($archiveSql);
        $stmt->bind_param("isssssss", $user['id'], $user['first_name'], $user['last_name'], $user['email'], $user['role'], $user['gender'], $user['address'], $user['phone']);

        if (!$stmt->execute()) {
            return false; // If archiving fails, return false
        }

        // ✅ Delete user from `users` table
        $deleteSql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($deleteSql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // ✅ GET ALL USERS
    public function getAllUsers() {
        $sql = "SELECT * FROM users";
        return $this->conn->query($sql);
    }

    // ✅ GET ARCHIVED USERS
    public function getAllArchivedUsers() {
        $sql = "SELECT * FROM archives";
        return $this->conn->query($sql);
    }
}
?>
