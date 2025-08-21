<?php
// app/controllers/ServerController.php
class ServerController extends Controller {
    public function index() {
        $this->requireAuth();
        
        // Pagination
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        // Build WHERE clause for filters
        $where = [];
        $params = [];
        
        if (!empty($_GET['search'])) {
            $where[] = "(server_name LIKE ? OR private_ip LIKE ? OR application_name LIKE ? OR server_functions LIKE ?)";
            $searchTerm = "%{$_GET['search']}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if (!empty($_GET['site'])) {
            $where[] = "site = ?";
            $params[] = $_GET['site'];
        }
        
        if (!empty($_GET['environment'])) {
            $where[] = "environment = ?";
            $params[] = $_GET['environment'];
        }
        
        if (!empty($_GET['asset_class'])) {
            $where[] = "asset_class = ?";
            $params[] = $_GET['asset_class'];
        }
        
        if (!empty($_GET['type'])) {
            $where[] = "type = ?";
            $params[] = $_GET['type'];
        }
        
        $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);
        
        // Get total count
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM servers $whereClause");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // Get servers with ordering
        $orderBy = $_GET['order_by'] ?? 'server_name';
        $orderDir = $_GET['order_dir'] ?? 'asc';
        
        // Validate order by field to prevent SQL injection
        $allowedOrderFields = ['server_name', 'site', 'environment', 'private_ip', 'application_name', 'asset_class', 'created_at'];
        $orderBy = in_array($orderBy, $allowedOrderFields) ? $orderBy : 'server_name';
        $orderDir = strtolower($orderDir) === 'desc' ? 'DESC' : 'ASC';
        
        $stmt = $this->db->prepare("
            SELECT * FROM servers 
            $whereClause 
            ORDER BY $orderBy $orderDir
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);
        $servers = $stmt->fetchAll();
        
        $totalPages = ceil($total / $perPage);
        
        $this->view('servers/index', [
            'servers' => $servers,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'filters' => $_GET,
            'orderBy' => $orderBy,
            'orderDir' => $orderDir
        ]);
    }
    
    public function view($id) {
        $this->requireAuth();
        
        $stmt = $this->db->prepare("SELECT * FROM servers WHERE id = ?");
        $stmt->execute([$id]);
        $server = $stmt->fetch();
        
        if (!$server) {
            $_SESSION['error_message'] = 'Server not found';
            $this->redirect('/servers');
        }
        
        // Get audit log for this server
        $stmt = $this->db->prepare("
            SELECT al.*, u.username 
            FROM audit_logs al 
            LEFT JOIN users u ON al.user_id = u.id 
            WHERE al.table_name = 'servers' AND al.record_id = ? 
            ORDER BY al.created_at DESC
        ");
        $stmt->execute([$id]);
        $auditLogs = $stmt->fetchAll();
        
        $this->view('servers/view', [
            'server' => $server,
            'auditLogs' => $auditLogs
        ]);
    }
    
    public function create() {
        $this->requireAuth();
        $this->requireRole('Editor');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            // Process form data
            $data = $this->prepareServerData($_POST);
            $data['created_by'] = $_SESSION['user_id'];
            
            // Validate required fields
            $errors = $this->validateServerData($data);
            
            if (!empty($errors)) {
                $this->view('servers/create', ['errors' => $errors, 'formData' => $_POST]);
                return;
            }
            
            // Check if server name already exists
            $stmt = $this->db->prepare("SELECT id FROM servers WHERE server_name = ?");
            $stmt->execute([$data['server_name']]);
            if ($stmt->fetch()) {
                $errors[] = 'Server name already exists';
                $this->view('servers/create', ['errors' => $errors, 'formData' => $_POST]);
                return;
            }
            
            // Insert server
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            
            try {
                $stmt = $this->db->prepare("INSERT INTO servers ($columns) VALUES ($placeholders)");
                $stmt->execute(array_values($data));
                
                // Log the action
                $this->logAction('CREATE', 'servers', $this->db->lastInsertId(), null, $data);
                
                $_SESSION['success_message'] = 'Server created successfully';
                $this->redirect('/servers');
            } catch (PDOException $e) {
                $errors[] = 'Error creating server: ' . $e->getMessage();
                $this->view('servers/create', ['errors' => $errors, 'formData' => $_POST]);
            }
        } else {
            $this->view('servers/create');
        }
    }
    
    public function edit($id) {
        $this->requireAuth();
        $this->requireRole('Editor');
        
        $stmt = $this->db->prepare("SELECT * FROM servers WHERE id = ?");
        $stmt->execute([$id]);
        $server = $stmt->fetch();
        
        if (!$server) {
            $_SESSION['error_message'] = 'Server not found';
            $this->redirect('/servers');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            // Get old values for audit log
            $oldValues = $server;
            
            // Process form data
            $data = $this->prepareServerData($_POST);
            
            // Validate required fields
            $errors = $this->validateServerData($data);
            
            if (!empty($errors)) {
                $this->view('servers/edit', ['server' => $server, 'errors' => $errors]);
                return;
            }
            
            // Check if server name already exists (excluding current server)
            if ($data['server_name'] !== $server['server_name']) {
                $stmt = $this->db->prepare("SELECT id FROM servers WHERE server_name = ? AND id != ?");
                $stmt->execute([$data['server_name'], $id]);
                if ($stmt->fetch()) {
                    $errors[] = 'Server name already exists';
                    $this->view('servers/edit', ['server' => $server, 'errors' => $errors]);
                    return;
                }
            }
            
            // Build SET clause
            $set = [];
            $params = [];
            foreach ($data as $column => $value) {
                $set[] = "$column = ?";
                $params[] = $value;
            }
            $params[] = $id;
            
            try {
                $stmt = $this->db->prepare("UPDATE servers SET " . implode(', ', $set) . " WHERE id = ?");
                $stmt->execute($params);
                
                // Log the action
                $this->logAction('UPDATE', 'servers', $id, $oldValues, $data);
                
                $_SESSION['success_message'] = 'Server updated successfully';
                $this->redirect('/servers');
            } catch (PDOException $e) {
                $errors[] = 'Error updating server: ' . $e->getMessage();
                $this->view('servers/edit', ['server' => $server, 'errors' => $errors]);
            }
        } else {
            $this->view('servers/edit', ['server' => $server]);
        }
    }
    
    public function delete($id) {
        $this->requireAuth();
        $this->requireRole('Editor');
        
        // Get server data for audit log
        $stmt = $this->db->prepare("SELECT * FROM servers WHERE id = ?");
        $stmt->execute([$id]);
        $server = $stmt->fetch();
        
        if ($server) {
            try {
                $stmt = $this->db->prepare("DELETE FROM servers WHERE id = ?");
                $stmt->execute([$id]);
                
                // Log the action
                $this->logAction('DELETE', 'servers', $id, $server, null);
                
                $_SESSION['success_message'] = 'Server deleted successfully';
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Error deleting server: ' . $e->getMessage();
            }
        } else {
            $_SESSION['error_message'] = 'Server not found';
        }
        
        $this->redirect('/servers');
    }
    
    private function prepareServerData($postData) {
        // Map form fields to database columns and sanitize
        $mapping = [
            'in_cj_asset' => 'bool',
            'server_name' => 'string',
            'type' => 'string',
            'environment' => 'string',
            'site' => 'string',
            'vm_cluster' => 'string',
            'private_ip' => 'string',
            'secondary_ip' => 'string',
            'public_ip' => 'string',
            'server_functions' => 'string',
            'application_name' => 'string',
            'server_model' => 'string',
            'cpu_vcpu' => 'string',
            'memory' => 'string',
            'hdd' => 'string',
            'os_family' => 'string',
            'distribution_edition' => 'string',
            'version' => 'string',
            'server_architecture' => 'string',
            'asset_type' => 'string',
            'service_category' => 'string',
            'business_service' => 'string',
            'system_end_user' => 'string',
            'server_pic' => 'string',
            'app_db_team_pic' => 'string',
            'db_app_pic' => 'string',
            'external_vendor_email' => 'string',
            'os_license_type' => 'string',
            'checklist' => 'string',
            'deployment_date' => 'date',
            'confidential' => 'int',
            'integrity' => 'int',
            'availability' => 'int',
            'finance' => 'bool',
            'reputation' => 'bool',
            'privacy' => 'bool',
            'regulatory' => 'bool',
            'service' => 'bool',
            'min_cj' => 'string',
            'patch_pic' => 'string',
            'patch_type' => 'string',
            'patch_schedule' => 'string',
            'patch_time' => 'time',
            'patch_frequency' => 'string',
            'reboot_policy' => 'string',
            'custom_group' => 'string',
            'auto_snapshot' => 'bool',
            'last_patch_deployed_date' => 'date',
            'target_distribution_edition' => 'string',
            'target_version' => 'string',
            'upgrade_migrate_pic' => 'string',
            'review_status' => 'string'
        ];
        
        $data = [];
        foreach ($mapping as $field => $type) {
            if (isset($postData[$field])) {
                switch ($type) {
                    case 'bool':
                        $data[$field] = (bool)$postData[$field];
                        break;
                    case 'int':
                        $data[$field] = (int)$postData[$field];
                        break;
                    case 'date':
                        $data[$field] = !empty($postData[$field]) ? $postData[$field] : null;
                        break;
                    case 'time':
                        $data[$field] = !empty($postData[$field]) ? $postData[$field] : null;
                        break;
                    default:
                        $data[$field] = trim($postData[$field]);
                }
            } else {
                // Set default values for checkboxes
                if ($type === 'bool') {
                    $data[$field] = false;
                }
            }
        }
        
        return $data;
    }
    
    private function validateServerData($data) {
        $errors = [];
        
        // Required fields validation
        if (empty($data['server_name'])) {
            $errors[] = 'Server name is required';
        }
        
        if (!empty($data['private_ip']) && !filter_var($data['private_ip'], FILTER_VALIDATE_IP)) {
            $errors[] = 'Private IP must be a valid IP address';
        }
        
        if (!empty($data['secondary_ip']) && !filter_var($data['secondary_ip'], FILTER_VALIDATE_IP)) {
            $errors[] = 'Secondary IP must be a valid IP address';
        }
        
        if (!empty($data['public_ip']) && !filter_var($data['public_ip'], FILTER_VALIDATE_IP)) {
            $errors[] = 'Public IP must be a valid IP address';
        }
        
        // Risk score validation
        if (isset($data['confidential']) && ($data['confidential'] < 1 || $data['confidential'] > 5)) {
            $errors[] = 'Confidential score must be between 1 and 5';
        }
        
        if (isset($data['integrity']) && ($data['integrity'] < 1 || $data['integrity'] > 5)) {
            $errors[] = 'Integrity score must be between 1 and 5';
        }
        
        if (isset($data['availability']) && ($data['availability'] < 1 || $data['availability'] > 5)) {
            $errors[] = 'Availability score must be between 1 and 5';
        }
        
        return $errors;
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