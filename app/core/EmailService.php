<?php
// app/core/EmailService.php
class EmailService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function sendDeploymentApprovalRequest($requestId, $approverRole) {
        $settings = $this->getEmailSettings();
        
        if ($settings['email_enabled'] !== '1') {
            return false;
        }
        
        // Get request details
        $stmt = $this->db->prepare("
            SELECT dr.*, u.username as requester_name, u.email as requester_email
            FROM deployment_requests dr
            LEFT JOIN users u ON dr.requested_by = u.id
            WHERE dr.id = ?
        ");
        $stmt->execute([$requestId]);
        $request = $stmt->fetch();
        
        if (!$request) {
            return false;
        }
        
        // Get approvers for the specified role
        $stmt = $this->db->prepare("
            SELECT email FROM users WHERE role = ? AND is_active = 1
        ");
        $stmt->execute([$approverRole]);
        $approvers = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($approvers)) {
            return false;
        }
        
        $to = implode(',', $approvers);
        $subject = "Deployment Request #{$requestId} Requires {$approverRole} Approval";
        
        $message = "A new deployment request requires your approval:\n\n";
        $message .= "Request ID: #{$requestId}\n";
        $message .= "Host Name: {$request['host_name']}\n";
        $message .= "Host IP: {$request['host_ip']}\n";
        $message .= "Environment: {$request['environment']}\n";
        $message .= "Requested By: {$request['requester_name']}\n";
        $message .= "Requested At: " . date('Y-m-d H:i', strtotime($request['created_at'])) . "\n\n";
        $message .= "Please review the request at: " . $this->getAppUrl() . "/deployments/view/{$requestId}\n\n";
        $message .= "This is an automated message from Server Management System.";
        
        return $this->sendEmail($to, $subject, $message, $settings);
    }
    
    public function sendDeploymentStatusUpdate($requestId, $status) {
        $settings = $this->getEmailSettings();
        
        if ($settings['email_enabled'] !== '1') {
            return false;
        }
        
        // Get request details
        $stmt = $this->db->prepare("
            SELECT dr.*, u.username as requester_name, u.email as requester_email
            FROM deployment_requests dr
            LEFT JOIN users u ON dr.requested_by = u.id
            WHERE dr.id = ?
        ");
        $stmt->execute([$requestId]);
        $request = $stmt->fetch();
        
        if (!$request) {
            return false;
        }
        
        $to = $request['requester_email'];
        $subject = "Deployment Request #{$requestId} Status Update: {$status}";
        
        $message = "Your deployment request status has been updated:\n\n";
        $message .= "Request ID: #{$requestId}\n";
        $message .= "Host Name: {$request['host_name']}\n";
        $message .= "Host IP: {$request['host_ip']}\n";
        $message .= "New Status: {$status}\n\n";
        
        if ($status === 'Approved') {
            $message .= "Your request has been approved. The server asset has been created automatically.\n";
        } elseif ($status === 'Rejected') {
            $message .= "Your request has been rejected. Please check the comments for more information.\n";
        }
        
        $message .= "\nView details at: " . $this->getAppUrl() . "/deployments/view/{$requestId}\n\n";
        $message .= "This is an automated message from Server Management System.";
        
        return $this->sendEmail($to, $subject, $message, $settings);
    }
    
    public function sendServerAuditAlert($serverId, $changes) {
        $settings = $this->getEmailSettings();
        
        if ($settings['email_enabled'] !== '1' || $settings['email_audit_alerts'] !== '1') {
            return false;
        }
        
        // Get server details
        $stmt = $this->db->prepare("
            SELECT s.*, u.username as modified_by
            FROM servers s
            LEFT JOIN users u ON s.updated_by = u.id
            WHERE s.id = ?
        ");
        $stmt->execute([$serverId]);
        $server = $stmt->fetch();
        
        if (!$server) {
            return false;
        }
        
        // Get administrators
        $stmt = $this->db->prepare("
            SELECT email FROM users WHERE role = 'Admin' AND is_active = 1
        ");
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($admins)) {
            return false;
        }
        
        $to = implode(',', $admins);
        $subject = "Server Audit Alert: {$server['server_name']}";
        
        $message = "Important changes were made to a server asset:\n\n";
        $message .= "Server: {$server['server_name']}\n";
        $message .= "IP Address: {$server['private_ip']}\n";
        $message .= "Modified By: {$server['modified_by']}\n";
        $message .= "Modified At: " . date('Y-m-d H:i', strtotime($server['updated_at'])) . "\n\n";
        $message .= "Changes:\n";
        
        foreach ($changes as $field => $change) {
            $message .= "- {$field}: {$change['old']} → {$change['new']}\n";
        }
        
        $message .= "\nView server details at: " . $this->getAppUrl() . "/servers/view/{$serverId}\n\n";
        $message .= "This is an automated alert from Server Management System.";
        
        return $this->sendEmail($to, $subject, $message, $settings);
    }
    
    private function sendEmail($to, $subject, $message, $settings) {
        try {
            $headers = "From: {$settings['email_from']}\r\n";
            $headers .= "Reply-To: {$settings['email_from']}\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            
            if (!empty($settings['email_cc'])) {
                $headers .= "\r\nCc: {$settings['email_cc']}";
            }
            
            if (!empty($settings['email_bcc'])) {
                $headers .= "\r\nBcc: {$settings['email_bcc']}";
            }
            
            return mail($to, $subject, $message, $headers);
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    private function getEmailSettings() {
        $stmt = $this->db->prepare("SELECT setting_key, setting_value FROM system_settings");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    
    private function getAppUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $base = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        return "{$protocol}://{$host}{$base}";
    }
}