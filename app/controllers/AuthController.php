<?php
// app/controllers/AuthController.php
class AuthController extends Controller {
    public function login() {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Get LDAP settings
            $ldapEnabled = $this->getSetting('ldap_enabled');
            
            // Try LDAP authentication first if enabled
            if ($ldapEnabled === '1') {
                $ldapResult = $this->authenticateWithLdap($username, $password);
                
                if ($ldapResult['success']) {
                    // Check if LDAP user exists in database or create them
                    $user = $this->getOrCreateLdapUser($username, $ldapResult['user_info']);
                    
                    if ($user && $user['is_active']) {
                        $this->setupUserSession($user);
                        $this->redirect('/dashboard');
                        return;
                    }
                }
            }
            
            // Fall back to local authentication
            $user = $this->authenticateLocally($username, $password);
            
            if ($user) {
                $this->setupUserSession($user);
                $this->redirect('/dashboard');
            } else {
                $error = "Invalid username or password";
                if ($ldapEnabled === '1') {
                    $error .= " (LDAP authentication also failed)";
                }
                $this->view('auth/login', ['error' => $error]);
            }
        } else {
            $this->view('auth/login');
        }
    }
    
    private function authenticateWithLdap($username, $password) {
        try {
            // Get LDAP settings
            $ldapHost = $this->getSetting('ldap_host');
            $ldapPort = $this->getSetting('ldap_port') ?: 389;
            $ldapBaseDn = $this->getSetting('ldap_base_dn');
            $ldapBindDn = $this->getSetting('ldap_bind_dn');
            $ldapBindPassword = $this->getSetting('ldap_bind_password');
            $ldapUserFilter = $this->getSetting('ldap_user_filter') ?: '(uid=%s)';
            
            // Connect to LDAP server
            $ldapConn = ldap_connect($ldapHost, $ldapPort);
            
            if (!$ldapConn) {
                return ['success' => false, 'message' => 'LDAP connection failed'];
            }
            
            ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);
            
            // Bind with service account or anonymously
            if (!empty($ldapBindDn) && !empty($ldapBindPassword)) {
                $bind = ldap_bind($ldapConn, $ldapBindDn, $ldapBindPassword);
            } else {
                $bind = ldap_bind($ldapConn);
            }
            
            if (!$bind) {
                return ['success' => false, 'message' => 'LDAP bind failed'];
            }
            
            // Search for user
            $filter = sprintf($ldapUserFilter, ldap_escape($username, '', LDAP_ESCAPE_FILTER));
            $search = ldap_search($ldapConn, $ldapBaseDn, $filter);
            $entries = ldap_get_entries($ldapConn, $search);
            
            if ($entries['count'] == 0) {
                return ['success' => false, 'message' => 'User not found in LDAP'];
            }
            
            $userDn = $entries[0]['dn'];
            
            // Try to bind with user credentials
            $userBind = ldap_bind($ldapConn, $userDn, $password);
            
            if (!$userBind) {
                return ['success' => false, 'message' => 'LDAP authentication failed'];
            }
            
            // Get user information
            $userInfo = [
                'username' => $username,
                'email' => $entries[0]['mail'][0] ?? $username . '@example.com',
                'display_name' => $entries[0]['displayname'][0] ?? $username,
                'groups' => $entries[0]['memberof'] ?? []
            ];
            
            ldap_close($ldapConn);
            
            return ['success' => true, 'user_info' => $userInfo];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function getOrCreateLdapUser($username, $userInfo) {
        // Check if user already exists
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Update user information from LDAP
            $stmt = $this->db->prepare("
                UPDATE users SET email = ?, is_ldap_user = 1, updated_at = NOW() 
                WHERE username = ?
            ");
            $stmt->execute([$userInfo['email'], $username]);
            return $user;
        }
        
        // Create new LDAP user with default role
        $defaultRole = $this->getSetting('ldap_default_role') ?: 'Viewer';
        
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password, role, is_ldap_user, is_active) 
            VALUES (?, ?, NULL, ?, 1, 1)
        ");
        $stmt->execute([$username, $userInfo['email'], $defaultRole]);
        
        // Get the newly created user
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    private function authenticateLocally($username, $password) {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE username = ? AND is_ldap_user = 0 AND is_active = 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    private function getSetting($key) {
        $stmt = $this->db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : null;
    }
    
    private function setupUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['is_ldap_user'] = $user['is_ldap_user'];
        
        // Update last login time
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
    }
    
    public function logout() {
        session_destroy();
        $this->redirect('/login');
    }
    
    public function profile() {
        $this->requireAuth();
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            $email = trim($_POST['email']);
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            $errors = [];
            
            // Validate email
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Valid email is required';
            }
            
            // Validate password change if requested
            if (!empty($newPassword)) {
                if (empty($currentPassword)) {
                    $errors[] = 'Current password is required to change password';
                } else if (!password_verify($currentPassword, $user['password'])) {
                    $errors[] = 'Current password is incorrect';
                } else if ($newPassword !== $confirmPassword) {
                    $errors[] = 'New passwords do not match';
                }
            }
            
            if (empty($errors)) {
                $query = "UPDATE users SET email = ?";
                $params = [$email];
                
                if (!empty($newPassword)) {
                    $query .= ", password = ?";
                    $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
                }
                
                $query .= " WHERE id = ?";
                $params[] = $_SESSION['user_id'];
                
                $stmt = $this->db->prepare($query);
                $stmt->execute($params);
                
                $_SESSION['user_email'] = $email;
                $_SESSION['success_message'] = 'Profile updated successfully';
                
                $this->redirect('/profile');
            } else {
                $this->view('auth/profile', ['user' => $user, 'errors' => $errors]);
            }
        } else {
            $this->view('auth/profile', ['user' => $user]);
        }
    }
}