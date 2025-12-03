<?php
class User {
    private $db;
    private $id;
    private $employeeId;
    private $username;
    private $role;
    private $employeeName;
    private $jobTitle;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function login($username, $password) {
        try {
            // Join with employees table to get employee info
            $stmt = $this->db->prepare("
                SELECT u.*, e.full_name, e.job_title, e.is_active 
                FROM users u 
                LEFT JOIN employees e ON u.employee_id = e.id 
                WHERE u.username = ?
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // TEMPORARY: Disable password hashing for testing
            // Compare plain text passwords
            if ($user && $user['password_hash'] === $password) {
                // Check if employee is active (if linked to employee)
                if ($user['employee_id'] && !$user['is_active']) {
                    return false;
                }
                
                $this->setUserData($user);
                $this->setSession();
                $this->updateLastLogin();
                return true;
            }
            
            // Fallback: Try password_verify for backwards compatibility
            if ($user && password_verify($password, $user['password_hash'])) {
                // Check if employee is active (if linked to employee)
                if ($user['employee_id'] && !$user['is_active']) {
                    return false;
                }
                
                $this->setUserData($user);
                $this->setSession();
                $this->updateLastLogin();
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
    
    private function updateLastLogin() {
        try {
            $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$this->id]);
        } catch(PDOException $e) {
            error_log("Update last login error: " . $e->getMessage());
        }
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public static function isAdmin() {
        return isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';
    }
    
    public static function isEmployee() {
        return isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'employee';
    }
    
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    public static function requireAdmin() {
        self::requireLogin();
        if (!self::isAdmin()) {
            header('Location: dashboard.php');
            exit();
        }
    }
    
    public static function logout() {
        session_destroy();
        header('Location: login.php');
        exit();
    }
    
    private function setUserData($user) {
        $this->id = $user['id'];
        $this->employeeId = $user['employee_id'];
        $this->username = $user['username'];
        $this->role = $user['role'];
        $this->employeeName = $user['full_name'] ?? 'Unknown';
        $this->jobTitle = $user['job_title'] ?? '';
    }
    
    private function setSession() {
        $_SESSION['user_id'] = $this->id;
        $_SESSION['employee_id'] = $this->employeeId;
        $_SESSION['username'] = $this->username;
        $_SESSION['role'] = $this->role;
        $_SESSION['employee_name'] = $this->employeeName;
        $_SESSION['job_title'] = $this->jobTitle;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getEmployeeId() { return $this->employeeId; }
    public function getUsername() { return $this->username; }
    public function getRole() { return $this->role; }
    public function getEmployeeName() { return $this->employeeName; }
    public function getJobTitle() { return $this->jobTitle; }
    
    // Get current user from session
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        $user = new self();
        $user->id = $_SESSION['user_id'];
        $user->employeeId = $_SESSION['employee_id'] ?? null;
        $user->username = $_SESSION['username'];
        $user->role = $_SESSION['role'];
        $user->employeeName = $_SESSION['employee_name'] ?? 'Unknown';
        $user->jobTitle = $_SESSION['job_title'] ?? '';
        
        return $user;
    }
}
?>
