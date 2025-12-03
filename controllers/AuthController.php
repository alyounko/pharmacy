<?php
class AuthController {
    private $user;
    
    public function __construct() {
        $this->user = new User();
    }
    
    public function login($username, $password) {
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'يرجى ملء جميع الحقول | Please fill in all fields.'];
        }
        
        if ($this->user->login($username, $password)) {
            return ['success' => true, 'message' => 'تم تسجيل الدخول بنجاح | Login successful.'];
        }
        
        return ['success' => false, 'message' => 'اسم المستخدم أو كلمة المرور غير صحيحة | Invalid username or password.'];
    }
    
    public function logout() {
        User::logout();
    }
}
?>
