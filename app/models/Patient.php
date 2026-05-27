<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Patient {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // 1. Fetch only patients belonging to this logged-in user
    public function getAll($userId) {
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE user_id = :user_id ORDER BY id DESC");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 2. Prevent accessing a patient ID if it doesn't belong to this user
    public function findById($id, $userId) {
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE id = :id AND user_id = :user_id LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // 3. Save the logged-in user's ID into the patient record during creation
    public function create($data, $userId) {
        $sql = "INSERT INTO patients (user_id, name, age, gender, phone, address, created_at, updated_at) 
                VALUES (:user_id, :name, :age, :gender, :phone, :address, NOW(), NOW())";
                
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':user_id', $userId,             PDO::PARAM_INT);
        $stmt->bindValue(':name',    $data['name']    ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':age',     $data['age']     ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':gender',  $data['gender']  ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':phone',   $data['phone']   ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':address', $data['address'] ?? null, PDO::PARAM_STR);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    // 4. Ensure a user can only update their own patient records
    public function update($id, $data, $userId) {
        $sql = "UPDATE patients 
                SET name = :name, age = :age, gender = :gender, phone = :phone, address = :address, updated_at = NOW() 
                WHERE id = :id AND user_id = :user_id";
                
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':id',      $id,                      PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId,                  PDO::PARAM_INT);
        $stmt->bindValue(':name',    $data['name']    ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':age',     $data['age']     ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':gender',  $data['gender']  ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':phone',   $data['phone']   ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':address', $data['address'] ?? null, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    // 5. Ensure a user can only delete their own patient records
    public function delete($id, $userId) {
        $stmt = $this->db->prepare("DELETE FROM patients WHERE id = :id AND user_id = :user_id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}