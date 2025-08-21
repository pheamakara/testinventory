<?php
// app/controllers/ApiController.php
class ApiController extends Controller {
    public function notifications() {
        $this->requireAuth();
        
        header('Content-Type: application/json');
        
        try {
            // Get pending approvals for current user
            $notifications = [];
            
            if ($this->hasRole('Security') || $this->hasRole('Admin')) {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as count 
                    FROM deployment_requests 
                    WHERE status = 'Pending Security'
                ");
                $stmt->execute();
                $securityPending = $stmt->fetchColumn();
                
                if ($securityPending > 0) {
                    $notifications[] = [
                        'icon' => 'fa-shield-alt',
                        'type' => 'warning',
                        'title' => 'Pending Security Approvals',
                        'message' => "You have $securityPending deployment requests waiting for security review",
                        'time' => 'Just now',
                        'link' => '/deployments?status=Pending Security'
                    ];
                }
            }
            
            if ($this->hasRole('Manager') || $this->hasRole('Admin')) {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as count 
                    FROM deployment_requests 
                    WHERE status = 'Pending Manager'
                ");
                $stmt->execute();
                $managerPending = $stmt->fetchColumn();
                
                if ($managerPending > 0) {
                    $notifications[] = [
                        'icon' => 'fa-tasks',
                        'type' => 'warning',
                        'title' => 'Pending Manager Approvals',
                        'message' => "You have $managerPending deployment requests waiting for manager approval",
                        'time' => 'Just now',
                        'link' => '/deployments?status=Pending Manager'
                    ];
                }
            }
            
            // Get recent audit alerts (last 24 hours)
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM audit_logs 
                WHERE table_name = 'servers' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND user_id != ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $recentAudits = $stmt->fetchColumn();
            
            if ($recentAudits > 0) {
                $notifications[] = [
                    'icon' => 'fa-history',
                    'type' => 'info',
                    'title' => 'Recent Server Changes',
                    'message' => "$recentAudits server modifications in the last 24 hours",
                    'time' => 'Today',
                    'link' => '/servers'
                ];
            }
            
            echo json_encode($notifications);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to load notifications']);
        }
    }
}