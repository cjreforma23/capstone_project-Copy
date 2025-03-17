<?php
require_once __DIR__ . '../Helpers/Database.php';

class Complaint {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    // Create Complaint
    public function createComplaint($user_id, $complaint_type, $subject, $description, $location, $priority, $attachment) {
        $sql = "INSERT INTO complaints (user_id, complaint_type, subject, description, location, priority, attachment, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issssss", $user_id, $complaint_type, $subject, $description, $location, $priority, $attachment);
        return $stmt->execute();
    }

    // Get all complaints (Admin & Staff)
    public function getAllComplaints() {
        $sql = "SELECT complaints.*, users.first_name, users.last_name FROM complaints 
                JOIN users ON complaints.user_id = users.id ORDER BY complaints.created_at DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get complaints by user (For Homeowners)
    public function getComplaintsByUser($user_id) {
        $sql = "SELECT * FROM complaints WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Update complaint status (For Admin/Staff)
    public function updateComplaintStatus($complaint_id, $status, $assigned_to = null, $resolution_notes = null) {
        $sql = "UPDATE complaints SET status = ?, assigned_to = ?, resolution_notes = ?, updated_at = NOW() WHERE complaint_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sisi", $status, $assigned_to, $resolution_notes, $complaint_id);
        return $stmt->execute();
    }
}
?>
