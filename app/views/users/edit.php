<!-- app/views/users/edit.php -->
<?php 
ob_start();
$pageTitle = "Edit User";
$csrfToken = $this->csrfToken();
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Edit User: <?php echo htmlspecialchars($user['username']); ?></h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">Update user account settings and permissions</p>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
    </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username *</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    <?php if (isset($errors) && in_array('Username is required', $errors)): ?>
                    <p class="text-sm text-red-600 mt-1">Username is required</p>
                    <?php endif; ?>
                    <?php if (isset($errors) && in_array('Username or email already exists', $errors)): ?>
                    <p class="text-sm text-red-600 mt-1">Username already exists</p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    <?php if (isset($errors) && in_array('Valid email is required', $errors)): ?>
                    <p class="text-sm text-red-600 mt-1">Valid email is required</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role *</label>
                    <select id="role" name="role" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                        <option value="Viewer" <?php echo $user['role'] === 'Viewer' ? 'selected' : ''; ?>>Viewer</option>
                        <option value="Editor" <?php echo $user['role'] === 'Editor' ? 'selected' : ''; ?>>Editor</option>
                        <option value="Security" <?php echo $user['role'] === 'Security' ? 'selected' : ''; ?>>Security</option>
                        <option value="Manager" <?php echo $user['role'] === 'Manager' ? 'selected' : ''; ?>>Manager</option>
                        <option value="Admin" <?php echo $user['role'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                    <?php if (isset($errors) && in_array('Role is required', $errors)): ?>
                    <p class="text-sm text-red-600 mt-1">Role is required</p>
                    <?php endif; ?>
                </div>
                
                <div class="flex items-end">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" 
                               <?php echo $user['is_active'] ? 'checked' : ''; ?>
                               class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Active Account</span>
                    </label>
                </div>
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_ldap_user" value="1" 
                           <?php echo $user['is_ldap_user'] ? 'checked' : ''; ?>
                           class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                           id="ldap-toggle">
                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">LDAP User Account</span>
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    LDAP users authenticate against your organization's directory service
                </p>
            </div>

            <div id="password-fields" class="border-t dark:border-gray-700 pt-6 mb-6 <?php echo $user['is_ldap_user'] ? 'hidden' : ''; ?>">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Change Password</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Password</label>
                        <input type="password" id="password" name="password"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave blank to keep current password</p>
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                        <?php if (isset($errors) && in_array('Passwords do not match', $errors)): ?>
                        <p class="text-sm text-red-600 mt-1">Passwords do not match</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="/users" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md">
                    Update User
                </button>
            </div>
        </form>
    </div>

    <!-- Account Information -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mt-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Account Information</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Account Created</label>
                <p class="text-sm text-gray-900 dark:text-white"><?php echo date('F j, Y, g:i a', strtotime($user['created_at'])); ?></p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</label>
                <p class="text-sm text-gray-900 dark:text-white"><?php echo date('F j, Y, g:i a', strtotime($user['updated_at'])); ?></p>
            </div>
            
            <?php if ($user['last_login']): ?>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Last Login</label>
                <p class="text-sm text-gray-900 dark:text-white"><?php echo date('F j, Y, g:i a', strtotime($user['last_login'])); ?></p>
            </div>
            <?php endif; ?>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">User ID</label>
                <p class="text-sm text-gray-900 dark:text-white"><?php echo $user['id']; ?></p>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle password fields based on LDAP setting
    document.getElementById('ldap-toggle').addEventListener('change', function() {
        const passwordFields = document.getElementById('password-fields');
        if (this.checked) {
            passwordFields.classList.add('hidden');
        } else {
            passwordFields.classList.remove('hidden');
        }
    });
</script>

<?php
$content = ob_get_clean();
include 'app/views/layout.php';