<!-- app/views/settings/index.php -->
<?php 
ob_start();
$pageTitle = "System Settings";
$csrfToken = $this->csrfToken();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">System Settings</h2>
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

<?php if (isset($_SESSION['warning_message'])): ?>
<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
    <?php echo $_SESSION['warning_message']; unset($_SESSION['warning_message']); ?>
</div>
<?php endif; ?>

<form method="POST" action="/settings/update" class="space-y-6">
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    
    <!-- LDAP Settings -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 border-b pb-2">LDAP Authentication</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2">
                <label class="flex items-center">
                    <input type="checkbox" name="setting_ldap_enabled" value="1" 
                           <?php echo ($settings['ldap_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>
                           class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Enable LDAP Authentication</span>
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Allow users to authenticate using LDAP/Active Directory
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">LDAP Host</label>
                <input type="text" name="setting_ldap_host" value="<?php echo htmlspecialchars($settings['ldap_host'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">LDAP Port</label>
                <input type="number" name="setting_ldap_port" value="<?php echo htmlspecialchars($settings['ldap_port'] ?? '389'); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Base DN</label>
                <input type="text" name="setting_ldap_base_dn" value="<?php echo htmlspecialchars($settings['ldap_base_dn'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                       placeholder="dc=example,dc=com">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bind DN (Optional)</label>
                <input type="text" name="setting_ldap_bind_dn" value="<?php echo htmlspecialchars($settings['ldap_bind_dn'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                       placeholder="cn=admin,dc=example,dc=com">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bind Password</label>
                <input type="password" name="setting_ldap_bind_password" value="<?php echo htmlspecialchars($settings['ldap_bind_password'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User Filter</label>
                <input type="text" name="setting_ldap_user_filter" value="<?php echo htmlspecialchars($settings['ldap_user_filter'] ?? '(uid=%s)'); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                       placeholder="(sAMAccountName=%s)">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Use %s as placeholder for username. Examples: (uid=%s), (sAMAccountName=%s), (mail=%s)
                </p>
            </div>
            
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Default Role for LDAP Users</label>
                <select name="setting_ldap_default_role" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    <option value="Viewer" <?php echo ($settings['ldap_default_role'] ?? 'Viewer') === 'Viewer' ? 'selected' : ''; ?>>Viewer</option>
                    <option value="Editor" <?php echo ($settings['ldap_default_role'] ?? 'Viewer') === 'Editor' ? 'selected' : ''; ?>>Editor</option>
                    <option value="Security" <?php echo ($settings['ldap_default_role'] ?? 'Viewer') === 'Security' ? 'selected' : ''; ?>>Security</option>
                    <option value="Manager" <?php echo ($settings['ldap_default_role'] ?? 'Viewer') === 'Manager' ? 'selected' : ''; ?>>Manager</option>
                </select>
            </div>
            
            <div class="col-span-2">
                <button type="button" onclick="testLdapConnection()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                    Test LDAP Connection
                </button>
                <div id="ldap-test-result" class="mt-2 text-sm hidden"></div>
            </div>
        </div>
    </div>
    
    <!-- Email Settings -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 border-b pb-2">Email Notifications</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2">
                <label class="flex items-center">
                    <input type="checkbox" name="setting_email_enabled" value="1" 
                           <?php echo ($settings['email_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>
                           class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Enable Email Notifications</span>
                </label>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SMTP Host</label>
                <input type="text" name="setting_email_host" value="<?php echo htmlspecialchars($settings['email_host'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SMTP Port</label>
                <input type="number" name="setting_email_port" value="<?php echo htmlspecialchars($settings['email_port'] ?? '587'); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SMTP Username</label>
                <input type="text" name="setting_email_username" value="<?php echo htmlspecialchars($settings['email_username'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SMTP Password</label>
                <input type="password" name="setting_email_password" value="<?php echo htmlspecialchars($settings['email_password'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Encryption</label>
                <select name="setting_email_encryption" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    <option value="">None</option>
                    <option value="tls" <?php echo ($settings['email_encryption'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                    <option value="ssl" <?php echo ($settings['email_encryption'] ?? 'tls') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Email</label>
                <input type="email" name="setting_email_from" value="<?php echo htmlspecialchars($settings['email_from'] ?? 'noreply@example.com'); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Name</label>
                <input type="text" name="setting_email_from_name" value="<?php echo htmlspecialchars($settings['email_from_name'] ?? 'Server Management System'); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CC Addresses (Optional)</label>
                <input type="text" name="setting_email_cc" value="<?php echo htmlspecialchars($settings['email_cc'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                       placeholder="admin@example.com,manager@example.com">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">BCC Addresses (Optional)</label>
                <input type="text" name="setting_email_bcc" value="<?php echo htmlspecialchars($settings['email_bcc'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                       placeholder="archive@example.com">
            </div>
            
            <div class="col-span-2">
                <label class="flex items-center">
                    <input type="checkbox" name="setting_email_approval_notifications" value="1" 
                           <?php echo ($settings['email_approval_notifications'] ?? '1') === '1' ? 'checked' : ''; ?>
                           class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Send approval notifications</span>
                </label>
            </div>
            
            <div class="col-span-2">
                <label class="flex items-center">
                    <input type="checkbox" name="setting_email_audit_alerts" value="1" 
                           <?php echo ($settings['email_audit_alerts'] ?? '1') === '1' ? 'checked' : ''; ?>
                           class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Send audit alerts</span>
                </label>
            </div>
            
            <div class="col-span-2">
                <button type="button" onclick="testEmailConnection()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                    Test Email Connection
                </button>
                <div id="email-test-result" class="mt-2 text-sm hidden"></div>
            </div>
        </div>
    </div>
    
    <!-- Application Settings -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 border-b pb-2">Application Settings</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Application Name</label>
                <input type="text" name="setting_app_name" value="<?php echo htmlspecialchars($settings['app_name'] ?? 'Server Management System'); ?>"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Timezone</label>
                <select name="setting_app_timezone" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    <?php $timezones = DateTimeZone::listIdentifiers(); ?>
                    <?php foreach ($timezones as $tz): ?>
                    <option value="<?php echo $tz; ?>" <?php echo ($settings['app_timezone'] ?? 'UTC') === $tz ? 'selected' : ''; ?>>
                        <?php echo $tz; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-span-2">
                <label class="flex items-center">
                    <input type="checkbox" name="setting_app_maintenance_mode" value="1" 
                           <?php echo ($settings['app_maintenance_mode'] ?? '0') === '1' ? 'checked' : ''; ?>
                           class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Maintenance Mode</span>
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    When enabled, only administrators can access the system
                </p>
            </div>
        </div>
    </div>
    
    <div class="flex justify-end">
        <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md">
            Save Settings
        </button>
    </div>
</form>

<script>
function testLdapConnection() {
    const formData = new FormData();
    formData.append('csrf_token', '<?php echo $csrfToken; ?>');
    
    // Collect all LDAP settings
    document.querySelectorAll('input[name^="setting_ldap_"], select[name^="setting_ldap_"]').forEach(input => {
        formData.append(input.name, input.value);
    });
    
    fetch('/settings/test-ldap', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const resultDiv = document.getElementById('ldap-test-result');
        resultDiv.classList.remove('hidden');
        
        if (data.success) {
            resultDiv.className = 'mt-2 text-sm text-green-600';
            resultDiv.innerHTML = '<i class="fas fa-check-circle mr-1"></i> ' + data.message;
        } else {
            resultDiv.className = 'mt-2 text-sm text-red-600';
            resultDiv.innerHTML = '<i class="fas fa-times-circle mr-1"></i> ' + data.message;
        }
    })
    .catch(error => {
        const resultDiv = document.getElementById('ldap-test-result');
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'mt-2 text-sm text-red-600';
        resultDiv.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Test failed: ' + error.message;
    });
}

function testEmailConnection() {
    const formData = new FormData();
    formData.append('csrf_token', '<?php echo $csrfToken; ?>');
    
    // Collect all email settings
    document.querySelectorAll('input[name^="setting_email_"], select[name^="setting_email_"]').forEach(input => {
        formData.append(input.name, input.value);
    });
    
    fetch('/settings/test-email', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const resultDiv = document.getElementById('email-test-result');
        resultDiv.classList.remove('hidden');
        
        if (data.success) {
            resultDiv.className = 'mt-2 text-sm text-green-600';
            resultDiv.innerHTML = '<i class="fas fa-check-circle mr-1"></i> ' + data.message;
        } else {
            resultDiv.className = 'mt-2 text-sm text-red-600';
            resultDiv.innerHTML = '<i class="fas fa-times-circle mr-1"></i> ' + data.message;
        }
    })
    .catch(error => {
        const resultDiv = document.getElementById('email-test-result');
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'mt-2 text-sm text-red-600';
        resultDiv.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Test failed: ' + error.message;
    });
}
</script>

<?php
$content = ob_get_clean();
include 'app/views/layout.php';
?>