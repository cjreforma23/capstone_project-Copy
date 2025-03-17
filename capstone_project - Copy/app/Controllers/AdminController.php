<?php
require_once __DIR__ . '/../Models/AdminModel.php';
require_once __DIR__ . '/../Helpers/database.php';

class AdminController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // CREATE User
    public function createUser($firstName, $lastName, $email, $password, $role, $address, $phone, $gender, $profilePicture = null) {
        // Validate input
        if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($role) || empty($address) || empty($phone) || empty($gender)) {
            return false;
        }

        // Check if email already exists
        $checkSql = "SELECT id FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($checkSql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return false;
        }

        // Create new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $status = 'active'; // Default status
        
        $sql = "INSERT INTO users (first_name, last_name, email, password, role, status, gender, address, phone, profile_picture) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $firstName, $lastName, $email, $hashedPassword, $role, $status, $gender, $address, $phone, $profilePicture);
        
        return $stmt->execute();
    }

    // READ (Get Single User by ID)
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // UPDATE User
    public function updateUser($id, $firstName, $lastName, $email, $role, $address, $phone, $gender, $profilePicture = null, $status = 'active') {
        $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, address = ?, phone = ?, gender = ?, profile_picture = ?, updated_at = NOW(), status = ?, WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssi", $firstName, $lastName, $email, $role, $address, $phone, $gender, $id, $profilePicture );
        return $stmt->execute();
    }

    // DELETE User
    public function deleteUser($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // LIST All Users (for Admin Dashboard)
    public function getAllUsers() {
        $sql = "SELECT * FROM users";
        return $this->conn->query($sql);
    }

    public function searchUsers($searchTerm = '', $role = '') {
        $query = "SELECT * FROM users WHERE 1=1";
        $params = [];
        $types = '';
    
        if (!empty($searchTerm)) {
            $query .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
            $searchTerm = "%$searchTerm%";
            $params[] = &$searchTerm;
            $params[] = &$searchTerm;
            $params[] = &$searchTerm;
            $types .= 'sss';
        }
    
        if (!empty($role)) {
            $query .= " AND role = ?";
            $params[] = &$role;
            $types .= 's';
        }
    
        $stmt = $this->conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
    
        $stmt->execute();
        return $stmt->get_result();
    }
    
}
