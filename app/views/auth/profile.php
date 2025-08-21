<!-- app/views/auth/profile.php -->
<?php 
ob_start();
$pageTitle = "User Profile";
$csrfToken = $this->csrfToken();
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">User Profile</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">Manage your account settings and preferences</p>
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400" disabled>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Username cannot be changed</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['role']); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400" disabled>
                </div>
            </div>

            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address *</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                <?php if (isset($errors) && in_array('Valid email is required', $errors)): ?>
                <p class="text-sm text-red-600 mt-1">Valid email is required</p>
                <?php endif; ?>
            </div>

            <div class="border-t dark:border-gray-700 pt-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Change Password</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Password</label>
                        <input type="password" id="current_password" name="current_password"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                        <?php if (isset($errors) && in_array('Current password is required to change password', $errors)): ?>
                        <p class="text-sm text-red-600 mt-1">Current password is required to change password</p>
                        <?php endif; ?>
                        <?php if (isset($errors) && in_array('Current password is incorrect', $errors)): ?>
                        <p class="text-sm text-red-600 mt-1">Current password is incorrect</p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Password</label>
                        <input type="password" id="new_password" name="new_password"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave blank to keep current password</p>
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                        <?php if (isset($errors) && in_array('New passwords do not match', $errors)): ?>
                        <p class="text-sm text-red-600 mt-1">New passwords do not match</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($user['is_ldap_user']): ?>
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">LDAP User Account</h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                            <p>This account is managed through LD/Active Directory. Some profile settings may be controlled by your system administrator.</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md">
                    Update Profile
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
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Authentication Type</label>
                <p class="text-sm text-gray-900 dark:text-white">
                    <?php echo $user['is_ldap_user'] ? 'LDAP/Active Directory' : 'Local Account'; ?>
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Account Status</label>
                <p class="text-sm text-gray-900 dark:text-white">
                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">User ID</label>
                <p class="text-sm text-gray-900 dark:text-white"><?php echo $user['id']; ?></p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'app/views/layouts/main.php';