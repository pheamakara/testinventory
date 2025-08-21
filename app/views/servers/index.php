<!-- app/views/servers/index.php -->
<?php 
ob_start();
$pageTitle = "Server Assets";
$csrfToken = $this->csrfToken();
?>

<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Server Assets</h2>
    <div class="flex space-x-2">
        <a href="/servers/export" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md flex items-center">
            <i class="fas fa-file-export mr-2"></i> Export
        </a>
        <?php if ($this->hasRole('Editor')): ?>
        <a href="/servers/import" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center">
            <i class="fas fa-file-import mr-2"></i> Import
        </a>
        <a href="/servers/create" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md flex items-center">
            <i class="fas fa-plus-circle mr-2"></i> Add Server
        </a>
        <?php endif; ?>
    </div>
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

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6 p-4">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>" 
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white" 
                   placeholder="Server name, IP, app...">
        </div>
        
        <div>
            <label for="site" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Site</label>
            <select id="site" name="site" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                <option value="">All Sites</option>
                <option value="HQ" <?php echo isset($filters['site']) && $filters['site'] === 'HQ' ? 'selected' : ''; ?>>HQ</option>
                <option value="TKK" <?php echo isset($filters['site']) && $filters['site'] === 'TKK' ? 'selected' : ''; ?>>TKK</option>
                <option value="Nehru" <?php echo isset($filters['site']) && $filters['site'] === 'Nehru' ? 'selected' : ''; ?>>Nehru</option>
            </select>
        </div>
        
        <div>
            <label for="environment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Environment</label>
            <select id="environment" name="environment" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                <option value="">All Environments</option>
                <option value="Production" <?php echo isset($filters['environment']) && $filters['environment'] === 'Production' ? 'selected' : ''; ?>>Production</option>
                <option value="Staging" <?php echo isset($filters['environment']) && $filters['environment'] === 'Staging' ? 'selected' : ''; ?>>Staging</option>
                <option value="Development" <?php echo isset($filters['environment']) && $filters['environment'] === 'Development' ? 'selected' : ''; ?>>Development</option>
            </select>
        </div>
        
        <div>
            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
            <select id="type" name="type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                <option value="">All Types</option>
                <option value="Physical" <?php echo isset($filters['type']) && $filters['type'] === 'Physical' ? 'selected' : ''; ?>>Physical</option>
                <option value="Virtual" <?php echo isset($filters['type']) && $filters['type'] === 'Virtual' ? 'selected' : ''; ?>>Virtual</option>
            </select>
        </div>
        
        <div>
            <label for="asset_class" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Asset Class</label>
            <select id="asset_class" name="asset_class" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                <option value="">All Classes</option>
                <option value="CJ" <?php echo isset($filters['asset_class']) && $filters['asset_class'] === 'CJ' ? 'selected' : ''; ?>>CJ</option>
                <option value="C1" <?php echo isset($filters['asset_class']) && $filters['asset_class'] === 'C1' ? 'selected' : ''; ?>>C1</option>
                <option value="C2" <?php echo isset($filters['asset_class']) && $filters['asset_class'] === 'C2' ? 'selected' : ''; ?>>C2</option>
                <option value="NC" <?php echo isset($filters['asset_class']) && $filters['asset_class'] === 'NC' ? 'selected' : ''; ?>>NC</option>
            </select>
        </div>
        
        <div class="flex items-end">
            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md w-full">
                Apply Filters
            </button>
        </div>
    </form>
</div>

<!-- Server Table -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        <a href="?<?php echo http_build_query(array_merge($filters, ['order_by' => 'server_name', 'order_dir' => $orderBy === 'server_name' && $orderDir === 'ASC' ? 'desc' : 'asc'])); ?>" class="flex items-center">
                            Server Name
                            <?php if ($orderBy === 'server_name'): ?>
                            <i class="fas fa-arrow-<?php echo $orderDir === 'ASC' ? 'up' : 'down'; ?> ml-1"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        <a href="?<?php echo http_build_query(array_merge($filters, ['order_by' => 'site', 'order_dir' => $orderBy === 'site' && $orderDir === 'ASC' ? 'desc' : 'asc'])); ?>" class="flex items-center">
                            Site
                            <?php if ($orderBy === 'site'): ?>
                            <i class="fas fa-arrow-<?php echo $orderDir === 'ASC' ? 'up' : 'down'; ?> ml-1"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        <a href="?<?php echo http_build_query(array_merge($filters, ['order_by' => 'environment', 'order_dir' => $orderBy === 'environment' && $orderDir === 'ASC' ? 'desc' : 'asc'])); ?>" class="flex items-center">
                            Environment
                            <?php if ($orderBy === 'environment'): ?>
                            <i class="fas fa-arrow-<?php echo $orderDir === 'ASC' ? 'up' : 'down'; ?> ml-1"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        <a href="?<?php echo http_build_query(array_merge($filters, ['order_by' => 'private_ip', 'order_dir' => $orderBy === 'private_ip' && $orderDir === 'ASC' ? 'desc' : 'asc'])); ?>" class="flex items-center">
                            Private IP
                            <?php if ($orderBy === 'private_ip'): ?>
                            <i class="fas fa-arrow-<?php echo $orderDir === 'ASC' ? 'up' : 'down'; ?> ml-1"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        <a href="?<?php echo http_build_query(array_merge($filters, ['order_by' => 'application_name', 'order_dir' => $orderBy === 'application_name' && $orderDir === 'ASC' ? 'desc' : 'asc'])); ?>" class="flex items-center">
                            Application
                            <?php if ($orderBy === 'application_name'): ?>
                            <i class="fas fa-arrow-<?php echo $orderDir === 'ASC' ? 'up' : 'down'; ?> ml-1"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        <a href="?<?php echo http_build_query(array_merge($filters, ['order_by' => 'asset_class', 'order_dir' => $orderBy === 'asset_class' && $orderDir === 'ASC' ? 'desc' : 'asc'])); ?>" class="flex items-center">
                            Asset Class
                            <?php if ($orderBy === 'asset_class'): ?>
                            <i class="fas fa-arrow-<?php echo $orderDir === 'ASC' ? 'up' : 'down'; ?> ml-1"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (empty($servers)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                        No servers found. <?php if (!empty($filters)): ?>Try adjusting your filters.<?php endif; ?>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($servers as $server): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                        <a href="/servers/view/<?php echo $server['id']; ?>" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                            <?php echo htmlspecialchars($server['server_name']); ?>
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($server['site']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($server['environment']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($server['private_ip']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($server['application_name']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?php echo $server['asset_class'] === 'CJ' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : ''; ?>
                            <?php echo $server['asset_class'] === 'C1' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : ''; ?>
                            <?php echo $server['asset_class'] === 'C2' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : ''; ?>
                            <?php echo $server['asset_class'] === 'NC' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ''; ?>
                        ">
                            <?php echo $server['asset_class']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="/servers/edit/<?php echo $server['id']; ?>" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 mr-3">Edit</a>
                        <?php if ($this->hasRole('Editor')): ?>
                        <a href="/servers/delete/<?php echo $server['id']; ?>" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" onclick="return confirm('Are you sure you want to delete this server?')">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700">
        <div class="flex-1 flex justify-between sm:hidden">
            <a href="?page=<?php echo $page > 1 ? $page - 1 : 1; ?>&<?php echo http_build_query($filters); ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">Previous</a>
            <a href="?page=<?php echo $page < $totalPages ? $page + 1 : $totalPages; ?>&<?php echo http_build_query($filters); ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">Next</a>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    Showing <span class="font-medium"><?php echo (($page - 1) * 50) + 1; ?></span> to <span class="font-medium"><?php echo min($page * 50, $total); ?></span> of <span class="font-medium"><?php echo $total; ?></span> results
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <a href="?page=<?php echo $page > 1 ? $page - 1 : 1; ?>&<?php echo http_build_query($filters); ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <span class="sr-only">Previous</span>
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($page + 2, $totalPages); $i++): ?>
                    <a href="?page=<?php echo $i; ?>&<?php echo http_build_query($filters); ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium <?php echo $i == $page ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <a href="?page=<?php echo $page < $totalPages ? $page + 1 : $totalPages; ?>&<?php echo http_build_query($filters); ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <span class="sr-only">Next</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </nav>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include 'app/views/layout.php';
?>