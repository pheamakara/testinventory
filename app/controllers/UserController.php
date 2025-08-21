<?php
// app/controllers/UserController.php
class UserController extends Controller {
    public function index() {
        $this->requireAuth();
        $this->requireRole('Admin');
        
        // Pagination
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Search functionality
        $search = $_GET['search'] ?? '';
        $where = '';
        $params = [];
        
        if (!empty($search)) {
            $where = "WHERE username LIKE ? OR email LIKE ? OR role LIKE ?";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }
        
        // Get total count
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM users $where");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // Get users
        $stmt = $this->db->prepare("
            SELECT id, username, email, role, is_ldap_user, is_active, created_at, updated_at 
            FROM users 
            $where 
            ORDER BY username 
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        $totalPages = ceil($total / $perPage);
        
        $this->view('users/index', [
            'users' => $users,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search
        ]);
    }
    
    public function create() {
        $this->requireAuth();
        $this->requireRole('Admin');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            $role = $_POST['role'];
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $isLdapUser = isset($_POST['is_ldap_user']) ? 1 : 0;
            
            // Validate inputs
            $errors = $this->validateUserData($_POST);
            
            if (empty($errors)) {
                // Handle LDAP user creation
                if ($isLdapUser) {
                    // For LDAP users, we don't store password locally
                    $hashedPassword = null;
                } else {
                    if (empty($password)) {
                        $errors[] = 'Password is required for non-LDAP users';
                    } else if ($password !== $confirmPassword) {
                        $errors[] = 'Passwords do not match';
                    } else {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    }
                }
                
                if (empty($errors)) {
                    $stmt = $this->db->prepare("
                        INSERT INTO users (username, email, password, role, is_ldap_user, is_active) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$username, $email, $hashedPassword, $role, $isLdapUser, $isActive]);
                    
                    // Log the action
                    $this->logAction('CREATE', 'users', $this->db->lastInsertId(), null, [
                        'username' => $username,
                        'email' => $email,
                        'role' => $role,
                        'is_ldap_user' => $isLdapUser,
                        'is_active' => $isActive
                    ]);
                    
                    $_SESSION['success_message'] = 'User created successfully';
                    $this->redirect('/users');
                }
            }
            
            $this->view('users/create', ['errors' => $errors, 'formData' => $_POST]);
        } else {
            $this->view('users/create');
        }
    }
    
    public function edit($id) {
        $this->requireAuth();
        $this->requireRole('Admin');
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $_SESSION['error_message'] = 'User not found';
            $this->redirect('/users');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $role = $_POST['role'];
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $isLdapUser = isset($_POST['is_ldap_user']) ? 1 : 0;
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            
            // Validate inputs
            $errors = $this->validateUserData($_POST, $id);
            
            if (empty($errors)) {
                // Prepare update query
                $query = "UPDATE users SET username = ?, email = ?, role = ?, is_ldap_user = ?, is_active = ?";
                $params = [$username, $email, $role, $isLdapUser, $isActive];
                
                // Update password if provided and not LDAP user
                if (!$isLdapUser && !empty($password)) {
                    if ($password !== $confirmPassword) {
                        $errors[] = 'Passwords do not match';
                    } else {
                        $query .= ", password = ?";
                        $params[] = password_hash($password, PASSWORD_DEFAULT);
                    }
                } else if ($isLdapUser) {
                    // Clear password for LDAP users
                    $query .= ", password = NULL";
                }
                
                if (empty($errors)) {
                    $query .= " WHERE id = ?";
                    $params[] = $id;
                    
                    $stmt = $this->db->prepare($query);
                    $stmt->execute($params);
                    
                    // Log the action
                    $this->logAction('UPDATE', 'users', $id, $user, [
                        'username' => $username,
                        'email' => $email,
                        'role' => $role,
                        'is_ldap_user' => $isLdapUser,
                        'is_active' => $isActive
                    ]);
                    
                    $_SESSION['success_message'] = 'User updated successfully';
                    $this->redirect('/users');
                }
            }
            
            $this->view('users/edit', ['user' => $user, 'errors' => $errors]);
        } else {
            $this->view('users/edit', ['user' => $user]);
        }
    }
    
    public function delete($id) {
        $this->requireAuth();
        $this->requireRole('Admin');
        
        // Prevent deleting own account
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error_message'] = 'Cannot delete your own account';
            $this->redirect('/users');
        }
        
        // Get user data for audit log
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if ($user) {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            
            // Log the action
            $this->logAction('DELETE', 'users', $id, $user, null);
            
            $_SESSION['success_message'] = 'User deleted successfully';
        } else {
            $_SESSION['error_message'] = 'User not found';
        }
        
        $this->redirect('/users');
    }
    
    private function validateUserData($data, $userId = null) {
        $errors = [];
        $username = trim($data['username']);
        $email = trim($data['email']);
        $role = $data['role'];
        $isLdapUser = isset($data['is_ldap_user']) ? 1 : 0;
        
        if (empty($username)) {
            $errors[] = 'Username is required';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }
        
        if (empty($role)) {
            $errors[] = 'Role is required';
        }
        
        // Check if username or email already exists (excluding current user)
        $stmt = $this->db->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$username, $email, $userId]);
        if ($stmt->fetch()) {
            $errors[] = 'Username or email already exists';
        }
        
        // For non-LDAP users, validate password if creating new user or changing password
        if (!$isLdapUser && empty($userId) && empty($data['password'])) {
            $errors[] = 'Password is required for non-LDAP users';
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