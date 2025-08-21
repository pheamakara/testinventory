<?php
// app/controllers/SettingsController.php
class SettingsController extends Controller {
    public function index() {
        $this->requireAuth();
        $this->requireRole('Admin');
        
        // Get current settings
        $stmt = $this->db->prepare("SELECT * FROM system_settings");
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $this->view('settings/index', ['settings' => $settings]);
    }
    
    public function update() {
        $this->requireAuth();
        $this->requireRole('Admin');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            // Get all settings from form
            $settings = [];
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'setting_') === 0) {
                    $settingKey = substr($key, 8); // Remove 'setting_' prefix
                    $settings[$settingKey] = trim($value);
                }
            }
            
            // Validate LDAP settings if enabled
            if (!empty($settings['ldap_enabled']) && $settings['ldap_enabled'] === '1') {
                $errors = $this->validateLdapSettings($settings);
                if (!empty($errors)) {
                    $_SESSION['error_message'] = implode('<br>', $errors);
                    $this->redirect('/settings');
                    return;
                }
            }
            
            // Validate email settings if enabled
            if (!empty($settings['email_enabled']) && $settings['email_enabled'] === '1') {
                $errors = $this->validateEmailSettings($settings);
                if (!empty($errors)) {
                    $_SESSION['error_message'] = implode('<br>', $errors);
                    $this->redirect('/settings');
                    return;
                }
            }
            
            // Update settings in database
            $this->db->beginTransaction();
            
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO system_settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?
                ");
                
                foreach ($settings as $key => $value) {
                    $stmt->execute([$key, $value, $value]);
                }
                
                $this->db->commit();
                
                // Log the action
                $this->logAction('UPDATE', 'system_settings', 0, null, $settings);
                
                $_SESSION['success_message'] = 'Settings updated successfully';
                
                // Test LDAP connection if enabled
                if (!empty($settings['ldap_enabled']) && $settings['ldap_enabled'] === '1') {
                    $ldapTest = $this->testLdapConnection($settings);
                    if (!$ldapTest['success']) {
                        $_SESSION['warning_message'] = 'Settings saved but LDAP test failed: ' . $ldapTest['message'];
                    } else {
                        $_SESSION['success_message'] .= '. LDAP connection test successful.';
                    }
                }
                
                // Test email connection if enabled
                if (!empty($settings['email_enabled']) && $settings['email_enabled'] === '1') {
                    $emailTest = $this->testEmailConnection($settings);
                    if (!$emailTest['success']) {
                        $_SESSION['warning_message'] = 'Settings saved but email test failed: ' . $emailTest['message'];
                    } else {
                        $_SESSION['success_message'] .= '. Email connection test successful.';
                    }
                }
                
            } catch (Exception $e) {
                $this->db->rollBack();
                $_SESSION['error_message'] = 'Error updating settings: ' . $e->getMessage();
            }
            
            $this->redirect('/settings');
        }
    }
    
    private function validateLdapSettings($settings) {
        $errors = [];
        
        if (empty($settings['ldap_host'])) {
            $errors[] = 'LDAP host is required';
        }
        
        if (empty($settings['ldap_base_dn'])) {
            $errors[] = 'LDAP base DN is required';
        }
        
        return $errors;
    }
    
    private function validateEmailSettings($settings) {
        $errors = [];
        
        if (empty($settings['email_host'])) {
            $errors[] = 'Email host is required';
        }
        
        if (empty($settings['email_port'])) {
            $errors[] = 'Email port is required';
        }
        
        if (empty($settings['email_username'])) {
            $errors[] = 'Email username is required';
        }
        
        if (!filter_var($settings['email_from'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid from email address is required';
        }
        
        return $errors;
    }
    
    private function testLdapConnection($settings) {
        try {
            $ldapConn = ldap_connect($settings['ldap_host'], $settings['ldap_port'] ?? 389);
            
            if (!$ldapConn) {
                return ['success' => false, 'message' => 'Could not connect to LDAP server'];
            }
            
            ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);
            
            // Try anonymous bind first, then with credentials if provided
            if (!empty($settings['ldap_bind_dn']) && !empty($settings['ldap_bind_password'])) {
                $bind = ldap_bind($ldapConn, $settings['ldap_bind_dn'], $settings['ldap_bind_password']);
            } else {
                $bind = ldap_bind($ldapConn);
            }
            
            if (!$bind) {
                return ['success' => false, 'message' => 'LDAP bind failed: ' . ldap_error($ldapConn)];
            }
            
            ldap_close($ldapConn);
            return ['success' => true, 'message' => 'LDAP connection successful'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function testEmailConnection($settings) {
        try {
            // Test SMTP connection
            $socket = fsockopen(
                $settings['email_host'], 
                $settings['email_port'], 
                $errno, 
                $errstr, 
                10
            );
            
            if (!$socket) {
                return ['success' => false, 'message' => "Could not connect to SMTP server: $errstr ($errno)"];
            }
            
            fclose($socket);
            return ['success' => true, 'message' => 'SMTP connection successful'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function testLdap() {
        $this->requireAuth();
        $this->requireRole('Admin');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            $settings = [];
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'setting_') === 0) {
                    $settingKey = substr($key, 8);
                    $settings[$settingKey] = trim($value);
                }
            }
            
            $result = $this->testLdapConnection($settings);
            
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => 'LDAP connection test successful']);
            } else {
                echo json_encode(['success' => false, 'message' => $result['message']]);
            }
        }
    }
    
    public function testEmail() {
        $this->requireAuth();
        $this->requireRole('Admin');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            $settings = [];
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'setting_') === 0) {
                    $settingKey = substr($key, 8);
                    $settings[$settingKey] = trim($value);
                }
            }
            
            $result = $this->testEmailConnection($settings);
            
            if ($result['success']) {
                // Try to send a test email
                $testResult = $this->sendTestEmail($settings);
                echo json_encode($testResult);
            } else {
                echo json_encode(['success' => false, 'message' => $result['message']]);
            }
        }
    }
    
    private function sendTestEmail($settings) {
        try {
            $to = $_SESSION['user_email'] ?? $settings['email_from'];
            $subject = 'Test Email from Server Management System';
            $message = "This is a test email sent from the Server Management System.\n\n";
            $message .= "Time: " . date('Y-m-d H:i:s') . "\n";
            $message .= "Settings:\n";
            $message .= "- Host: " . $settings['email_host'] . "\n";
            $message .= "- Port: " . $settings['email_port'] . "\n";
            
            $headers = "From: " . $settings['email_from'] . "\r\n";
            $headers .= "Reply-To: " . $settings['email_from'] . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            
            if (mail($to, $subject, $message, $headers)) {
                return ['success' => true, 'message' => 'Test email sent successfully to ' . $to];
            } else {
                return ['success' => false, 'message' => 'Failed to send test email'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function logAction($action, $table, $recordId, $oldValues, $newValues) {
        $stmt = $this->db->prepare("
            INSERT INTO audit_logs 
            (user_id, action, table_name, record_id, old_values, new_values) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $action,
            $table,
            $recordId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null
        ]);
    }
}