<?php
// app/controllers/DeploymentController.php
class DeploymentController extends Controller {
    public function index() {
        $this->requireAuth();
        
        // Pagination
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Build WHERE clause for filters
        $where = [];
        $params = [];
        
        // Users can only see their own requests unless they are approvers
        $userCanSeeAll = $this->hasRole('Security') || $this->hasRole('Manager') || $this->hasRole('Admin');
        
        if (!$userCanSeeAll) {
            $where[] = "requested_by = ?";
            $params[] = $_SESSION['user_id'];
        }
        
        if (!empty($_GET['status'])) {
            $where[] = "status = ?";
            $params[] = $_GET['status'];
        }
        
        if (!empty($_GET['site'])) {
            $where[] = "site = ?";
            $params[] = $_GET['site'];
        }
        
        if (!empty($_GET['environment'])) {
            $where[] = "environment = ?";
            $params[] = $_GET['environment'];
        }
        
        if (!empty($_GET['asset_criticality'])) {
            $where[] = "asset_criticality = ?";
            $params[] = $_GET['asset_criticality'];
        }
        
        $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);
        
        // Get total count
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM deployment_requests $whereClause");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // Get requests
        $stmt = $this->db->prepare("
            SELECT dr.*, u.username as requester_name 
            FROM deployment_requests dr
            LEFT JOIN users u ON dr.requested_by = u.id
            $whereClause 
            ORDER BY dr.created_at DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);
        $requests = $stmt->fetchAll();
        
        $totalPages = ceil($total / $perPage);
        
        $this->view('deployments/index', [
            'requests' => $requests,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'filters' => $_GET,
            'userCanSeeAll' => $userCanSeeAll
        ]);
    }
    
    public function create() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            // Validate input data
            $errors = $this->validateDeploymentData($_POST);
            
            if (!empty($errors)) {
                $this->view('deployments/create', ['errors' => $errors, 'formData' => $_POST]);
                return;
            }
            
            $data = [
                'host_ip' => $_POST['host_ip'],
                'host_name' => $_POST['host_name'],
                'rack_name' => $_POST['rack_name'],
                'server_type' => $_POST['server_type'],
                'environment' => $_POST['environment'],
                'vm_cluster' => $_POST['vm_cluster'],
                'site' => $_POST['site'],
                'asset_criticality' => $_POST['asset_criticality'],
                'requested_by' => $_SESSION['user_id']
            ];
            
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            
            $stmt = $this->db->prepare("INSERT INTO deployment_requests ($columns) VALUES ($placeholders)");
            $stmt->execute(array_values($data));
            
            $requestId = $this->db->lastInsertId();
            
            // Create checklist entries
            $this->initializeChecklist($requestId);
            
            // Log the action
            $this->logAction('CREATE', 'deployment_requests', $requestId, null, $data);
            
            $_SESSION['success_message'] = 'Deployment request created successfully';
            $this->redirect("/deployments/view/$requestId");
        }
        
        $this->view('deployments/create');
    }
    
    private function validateDeploymentData($data) {
        $errors = [];
        
        if (empty($data['host_name'])) {
            $errors[] = 'Host name is required';
        }
        
        if (empty($data['host_ip']) || !filter_var($data['host_ip'], FILTER_VALIDATE_IP)) {
            $errors[] = 'Valid host IP is required';
        }
        
        if (empty($data['server_type']) || !in_array($data['server_type'], ['Physical', 'Virtual'])) {
            $errors[] = 'Valid server type is required';
        }
        
        if (empty($data['environment']) || !in_array($data['environment'], ['Production', 'Staging'])) {
            $errors[] = 'Valid environment is required';
        }
        
        if (empty($data['site']) || !in_array($data['site'], ['HQ', 'TKK', 'Nehru'])) {
            $errors[] = 'Valid site is required';
        }
        
        if (empty($data['asset_criticality']) || !in_array($data['asset_criticality'], ['CJ1', 'CJ2', 'CJ3', 'NC'])) {
            $errors[] = 'Valid asset criticality is required';
        }
        
        return $errors;
    }
    
    private function initializeChecklist($requestId) {
        // Get all checklist items
        $stmt = $this->db->prepare("SELECT * FROM checklist_items");
        $stmt->execute();
        $items = $stmt->fetchAll();
        
        // Insert checklist responses
        $stmt = $this->db->prepare("
            INSERT INTO deployment_checklists 
            (deployment_request_id, checklist_item_id, status) 
            VALUES (?, ?, 'Not Completed')
        ");
        
        foreach ($items as $item) {
            // Only add items that match the server type
            $serverTypeStmt = $this->db->prepare("
                SELECT server_type FROM deployment_requests WHERE id = ?
            ");
            $serverTypeStmt->execute([$requestId]);
            $request = $serverTypeStmt->fetch();
            
            if ($item['category'] === 'Common' || 
                ($item['category'] === 'Virtual' && $request['server_type'] === 'Virtual') ||
                ($item['category'] === 'Physical' && $request['server_type'] === 'Physical')) {
                $stmt->execute([$requestId, $item['id']]);
            }
        }
    }
    
    public function view($id) {
        $this->requireAuth();
        
        // Get request
        $stmt = $this->db->prepare("
            SELECT dr.*, u.username as requester_name 
            FROM deployment_requests dr
            LEFT JOIN users u ON dr.requested_by = u.id
            WHERE dr.id = ?
        ");
        $stmt->execute([$id]);
        $request = $stmt->fetch();
        
        if (!$request) {
            $_SESSION['error_message'] = 'Deployment request not found';
            $this->redirect('/deployments');
        }
        
        // Check if user has permission to view this request
        $userCanView = $request['requested_by'] == $_SESSION['user_id'] || 
                      $this->hasRole('Security') || 
                      $this->hasRole('Manager') || 
                      $this->hasRole('Admin');
        
        if (!$userCanView) {
            $_SESSION['error_message'] = 'You do not have permission to view this request';
            $this->redirect('/deployments');
        }
        
        // Get checklist items with responses
        $stmt = $this->db->prepare("
            SELECT ci.*, dc.status, dc.comment, dc.completed_at, 
                   u.username as performed_by
            FROM checklist_items ci
            LEFT JOIN deployment_checklists dc ON ci.id = dc.checklist_item_id AND dc.deployment_request_id = ?
            LEFT JOIN users u ON dc.performed_by_user_id = u.id
            WHERE ci.category = 'Common' 
               OR (ci.category = 'Virtual' AND ? = 'Virtual')
               OR (ci.category = 'Physical' AND ? = 'Physical')
            ORDER BY ci.category, ci.id
        ");
        $stmt->execute([$id, $request['server_type'], $request['server_type']]);
        $checklist = $stmt->fetchAll();
        
        // Get approvals
        $stmt = $this->db->prepare("
            SELECT da.*, u.username as approver_name
            FROM deployment_approvals da
            LEFT JOIN users u ON da.approver_user_id = u.id
            WHERE da.deployment_request_id = ?
            ORDER BY da.created_at
        ");
        $stmt->execute([$id]);
        $approvals = $stmt->fetchAll();
        
        // Calculate completion percentage
        $totalItems = count($checklist);
        $completedItems = 0;
        foreach ($checklist as $item) {
            if ($item['status'] === 'Completed') {
                $completedItems++;
            }
        }
        $completionPercentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
        
        $this->view('deployments/view', [
            'request' => $request,
            'checklist' => $checklist,
            'approvals' => $approvals,
            'completionPercentage' => $completionPercentage,
            'userCanView' => $userCanView
        ]);
    }
    
    public function updateChecklist($id) {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method not allowed";
            return;
        }
        
        $this->validateCsrf();
        
        // Verify user can edit this request
        $stmt = $this->db->prepare("
            SELECT status, requested_by FROM deployment_requests WHERE id = ?
        ");
        $stmt->execute([$id]);
        $request = $stmt->fetch();
        
        if (!$request) {
            $_SESSION['error_message'] = 'Request not found';
            $this->redirect('/deployments');
        }
        
        // Only the requester can update checklist when in Draft status
        if ($request['status'] === 'Draft' && $request['requested_by'] != $_SESSION['user_id']) {
            $_SESSION['error_message'] = 'Only the requester can update checklist in Draft status';
            $this->redirect("/deployments/view/$id");
        }
        
        // Security/Manager can add comments even after submission
        $canAddComments = $this->hasRole('Security') || $this->hasRole('Manager') || $this->hasRole('Admin');
        
        if ($request['status'] !== 'Draft' && !$canAddComments) {
            $_SESSION['error_message'] = 'Cannot update checklist after submission';
            $this->redirect("/deployments/view/$id");
        }
        
        // Update checklist items
        foreach ($_POST['checklist'] as $itemId => $data) {
            $status = $data['status'] ?? 'Not Completed';
            $comment = $data['comment'] ?? '';
            
            // Security/Manager can only add comments, not change status
            if ($request['status'] !== 'Draft' && $canAddComments) {
                $stmt = $this->db->prepare("
                    UPDATE deployment_checklists 
                    SET comment = ?
                    WHERE deployment_request_id = ? AND checklist_item_id = ?
                ");
                $stmt->execute([$comment, $id, $itemId]);
            } else {
                $stmt = $this->db->prepare("
                    UPDATE deployment_checklists 
                    SET status = ?, comment = ?, 
                        performed_by_user_id = ?, completed_at = ?
                    WHERE deployment_request_id = ? AND checklist_item_id = ?
                ");
                
                $completedAt = $status === 'Completed' ? date('Y-m-d H:i:s') : null;
                $performedBy = $status === 'Completed' ? $_SESSION['user_id'] : null;
                
                $stmt->execute([
                    $status, $comment, $performedBy, $completedAt, 
                    $id, $itemId
                ]);
            }
        }
        
        $_SESSION['success_message'] = 'Checklist updated successfully';
        $this->redirect("/deployments/view/$id");
    }
    
    public function submit($id) {
        $this->requireAuth();
        
        // Verify user owns this request
        $stmt = $this->db->prepare("
            SELECT status, requested_by FROM deployment_requests WHERE id = ?
        ");
        $stmt->execute([$id]);
        $request = $stmt->fetch();
        
        if (!$request || $request['requested_by'] != $_SESSION['user_id']) {
            $_SESSION['error_message'] = 'Access denied';
            $this->redirect('/deployments');
        }
        
        if ($request['status'] !== 'Draft') {
            $_SESSION['error_message'] = 'Request already submitted';
            $this->redirect("/deployments/view/$id");
        }
        
        // Check if at least 80% of checklist is completed
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total, 
                   SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed
            FROM deployment_checklists 
            WHERE deployment_request_id = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        $completionPercentage = $result['total'] > 0 ? ($result['completed'] / $result['total']) * 100 : 0;
        
        if ($completionPercentage < 80) {
            $_SESSION['error_message'] = "Cannot submit request. Only $completionPercentage% of checklist completed. Minimum 80% required.";
            $this->redirect("/deployments/view/$id");
        }
        
        // Update status to Pending Security
        $stmt = $this->db->prepare("
            UPDATE deployment_requests SET status = 'Pending Security' WHERE id = ?
        ");
        $stmt->execute([$id]);
        
        // Log the action
        $this->logAction('UPDATE', 'deployment_requests', $id, $request, ['status' => 'Pending Security']);
        
        $_SESSION['success_message'] = 'Request submitted for Security approval';
        $this->redirect("/deployments/view/$id");
    }
    
    public function approve($id, $step) {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            $decision = $_POST['decision'];
            $comment = $_POST['comment'] ?? '';
            
            // Verify request exists and is in correct status
            $stmt = $this->db->prepare("SELECT * FROM deployment_requests WHERE id = ?");
            $stmt->execute([$id]);
            $request = $stmt->fetch();
            
            if (!$request) {
                $_SESSION['error_message'] = 'Request not found';
                $this->redirect('/deployments');
            }
            
            // Check if user has appropriate role
            $requiredRole = $step === 'security' ? 'Security' : 'Manager';
            if (!($this->hasRole($requiredRole) || $this->hasRole('Admin'))) {
                $_SESSION['error_message'] = "Access denied. Required role: $requiredRole";
                $this->redirect("/deployments/view/$id");
            }
            
            // Verify request is in correct status for this approval step
            $expectedStatus = $step === 'security' ? 'Pending Security' : 'Pending Manager';
            if ($request['status'] !== $expectedStatus) {
                $_SESSION['error_message'] = "Request is not in $expectedStatus status";
                $this->redirect("/deployments/view/$id");
            }
            
            // Record approval
            $stmt = $this->db->prepare("
                INSERT INTO deployment_approvals 
                (deployment_request_id, approver_role, approver_user_id, decision, comment)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id, $requiredRole, $_SESSION['user_id'], $decision, $comment
            ]);
            
            // Update request status
            $newStatus = $decision === 'Approved' 
                ? ($step === 'security' ? 'Pending Manager' : 'Approved')
                : 'Rejected';
                
            $stmt = $this->db->prepare("
                UPDATE deployment_requests SET status = ? WHERE id = ?
            ");
            $stmt->execute([$newStatus, $id]);
            
            // Log the action
            $this->logAction('UPDATE', 'deployment_requests', $id, $request, ['status' => $newStatus]);
            
            // If approved, create server asset
            if ($newStatus === 'Approved') {
                $this->createServerFromDeployment($id);
            }
            
            $_SESSION['success_message'] = "Request $decision successfully";
            $this->redirect("/deployments/view/$id");
        }
    }
    
    private function createServerFromDeployment($requestId) {
        // Get deployment request data
        $stmt = $this->db->prepare("SELECT * FROM deployment_requests WHERE id = ?");
        $stmt->execute([$requestId]);
        $request = $stmt->fetch();
        
        // Create server asset
        $serverData = [
            'server_name' => $request['host_name'],
            'type' => $request['server_type'],
            'environment' => $request['environment'],
            'site' => $request['site'],
            'vm_cluster' => $request['vm_cluster'],
            'private_ip' => $request['host_ip'],
            'asset_class' => $request['asset_criticality'],
            'created_by' => $_SESSION['user_id']
        ];
        
        $columns = implode(', ', array_keys($serverData));
        $placeholders = implode(', ', array_fill(0, count($serverData), '?'));
        
        $stmt = $this->db->prepare("INSERT INTO servers ($columns) VALUES ($placeholders)");
        $stmt->execute(array_values($serverData));
        
        $serverId = $this->db->lastInsertId();
        
        // Log the action
        $this->logAction('CREATE', 'servers', $serverId, null, $serverData);
        
        // Update deployment request with server ID
        $stmt = $this->db->prepare("
            UPDATE deployment_requests SET server_id = ? WHERE id = ?
        ");
        $stmt->execute([$serverId, $requestId]);
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