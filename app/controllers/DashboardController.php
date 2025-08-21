<?php
// app/controllers/DashboardController.php
class DashboardController extends Controller {
    public function index() {
        $this->requireAuth();
        
        // Get server counts by site
        $stmt = $this->db->prepare("
            SELECT site, COUNT(*) as count 
            FROM servers 
            GROUP BY site
        ");
        $stmt->execute();
        $serversBySite = $stmt->fetchAll();
        
        // Get server counts by type
        $stmt = $this->db->prepare("
            SELECT type, COUNT(*) as count 
            FROM servers 
            WHERE type IS NOT NULL
            GROUP BY type
        ");
        $stmt->execute();
        $serversByType = $stmt->fetchAll();
        
        // Get server counts by environment
        $stmt = $this->db->prepare("
            SELECT environment, COUNT(*) as count 
            FROM servers 
            WHERE environment IS NOT NULL
            GROUP BY environment
        ");
        $stmt->execute();
        $serversByEnvironment = $stmt->fetchAll();
        
        // Get deployment request counts by status
        $stmt = $this->db->prepare("
            SELECT status, COUNT(*) as count 
            FROM deployment_requests 
            GROUP BY status
        ");
        $stmt->execute();
        $deploymentsByStatus = $stmt->fetchAll();
        
        // Get pending approvals for current user
        $pendingApprovals = [];
        
        if ($this->hasRole('Security') || $this->hasRole('Admin')) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM deployment_requests 
                WHERE status = 'Pending Security'
            ");
            $stmt->execute();
            $securityPending = $stmt->fetchColumn();
            $pendingApprovals['Security'] = $securityPending;
        }
        
        if ($this->hasRole('Manager') || $this->hasRole('Admin')) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM deployment_requests 
                WHERE status = 'Pending Manager'
            ");
            $stmt->execute();
            $managerPending = $stmt->fetchColumn();
            $pendingApprovals['Manager'] = $managerPending;
        }
        
        // Get recent deployment requests
        $stmt = $this->db->prepare("
            SELECT dr.*, u.username as requester_name 
            FROM deployment_requests dr
            LEFT JOIN users u ON dr.requested_by = u.id
            ORDER BY dr.created_at DESC 
            LIMIT 10
        ");
        $stmt->execute();
        $recentDeployments = $stmt->fetchAll();
        
        // Get recent servers
        $stmt = $this->db->prepare("
            SELECT s.*, u.username as creator_name 
            FROM servers s
            LEFT JOIN users u ON s.created_by = u.id
            ORDER BY s.created_at DESC 
            LIMIT 10
        ");
        $stmt->execute();
        $recentServers = $stmt->fetchAll();
        
        $this->view('dashboard/index', [
            'serversBySite' => $serversBySite,
            'serversByType' => $serversByType,
            'serversByEnvironment' => $serversByEnvironment,
            'deploymentsByStatus' => $deploymentsByStatus,
            'pendingApprovals' => $pendingApprovals,
            'recentDeployments' => $recentDeployments,
            'recentServers' => $recentServers
        ]);
    }
}