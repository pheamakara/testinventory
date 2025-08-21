<!-- app/views/dashboard/index.php -->
<?php 
ob_start();
$pageTitle = "Dashboard";
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Servers Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">
                <i class="fas fa-server text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Servers</h3>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    <?php 
                    $stmt = $db->prepare("SELECT COUNT(*) FROM servers");
                    $stmt->execute();
                    echo $stmt->fetchColumn();
                    ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Total Deployment Requests Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400">
                <i class="fas fa-tasks text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Deployment Requests</h3>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    <?php 
                    $stmt = $db->prepare("SELECT COUNT(*) FROM deployment_requests");
                    $stmt->execute();
                    echo $stmt->fetchColumn();
                    ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Pending Approvals Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900/40 text-yellow-600 dark:text-yellow-400">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Approvals</h3>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    <?php 
                    $stmt = $db->prepare("SELECT COUNT(*) FROM deployment_requests WHERE status IN ('Pending Security', 'Pending Manager')");
                    $stmt->execute();
                    echo $stmt->fetchColumn();
                    ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Users Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Users</h3>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    <?php 
                    $stmt = $db->prepare("SELECT COUNT(*) FROM users");
                    $stmt->execute();
                    echo $stmt->fetchColumn();
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Pending Approvals Section -->
<?php if (!empty($pendingApprovals)): ?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <?php if (isset($pendingApprovals['Security'])): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Pending Security Approval</h3>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400"><?php echo $pendingApprovals['Security']; ?></p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Requests waiting for Security review</p>
            </div>
            <a href="/deployments?status=Pending Security" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-md">
                Review
            </a>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (isset($pendingApprovals['Manager'])): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Pending Manager Approval</h3>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400"><?php echo $pendingApprovals['Manager']; ?></p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Requests waiting for Manager approval</p>
            </div>
            <a href="/deployments?status=Pending Manager" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-md">
                Review
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Servers by Site Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Servers by Site</h3>
        <div class="space-y-3">
            <?php foreach ($serversBySite as $item): ?>
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo $item['site'] ?: 'Unknown'; ?></span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo $item['count']; ?></span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo ($item['count'] / max(1, array_sum(array_column($serversBySite, 'count'))) * 100; ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Servers by Type Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Servers by Type</h3>
        <div class="space-y-3">
            <?php foreach ($serversByType as $item): ?>
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo $item['type'] ?: 'Unknown'; ?></span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo $item['count']; ?></span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" style="width: <?php echo ($item['count'] / max(1, array_sum(array_column($serversByType, 'count'))) * 100; ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Servers by Environment Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Servers by Environment</h3>
        <div class="space-y-3">
            <?php foreach ($serversByEnvironment as $item): ?>
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo $item['environment'] ?: 'Unknown'; ?></span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo $item['count']; ?></span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-purple-600 h-2 rounded-full" style="width: <?php echo ($item['count'] / max(1, array_sum(array_column($serversByEnvironment, 'count'))) * 100; ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Deployment Requests by Status Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Deployment Requests by Status</h3>
        <div class="space-y-3">
            <?php foreach ($deploymentsByStatus as $item): ?>
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo $item['status']; ?></span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo $item['count']; ?></span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="
                        <?php echo $item['status'] === 'Approved' ? 'bg-green-600' : ''; ?>
                        <?php echo $item['status'] === 'Pending Security' || $item['status'] === 'Pending Manager' ? 'bg-yellow-600' : ''; ?>
                        <?php echo $item['status'] === 'Rejected' ? 'bg-red-600' : ''; ?>
                        <?php echo $item['status'] === 'Draft' ? 'bg-gray-600' : ''; ?>
                        h-2 rounded-full" style="width: <?php echo ($item['count'] / max(1, array_sum(array_column($deploymentsByStatus, 'count'))) * 100; ?>%">
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Recent Activity Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Deployment Requests -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Recent Deployment Requests</h3>
        <div class="space-y-3">
            <?php if (empty($recentDeployments)): ?>
            <p class="text-sm text-gray-500 dark:text-gray-400">No recent deployment requests</p>
            <?php else: ?>
            <?php foreach ($recentDeployments as $request): ?>
            <div class="flex items-center justify-between py-2 border-b dark:border-gray-700 last:border-b-0">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($request['host_name']); ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        By <?php echo htmlspecialchars($request['requester_name']); ?> • 
                        <?php echo date('M j, Y', strtotime($request['created_at'])); ?>
                    </p>
                </div>
                <span class="px-2 py-1 text-xs font-medium rounded-full 
                    <?php echo $request['status'] === 'Approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ''; ?>
                    <?php echo $request['status'] === 'Pending Security' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : ''; ?>
                    <?php echo $request['status'] === 'Pending Manager' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : ''; ?>
                    <?php echo $request['status'] === 'Rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : ''; ?>
                    <?php echo $request['status'] === 'Draft' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' : ''; ?>
                ">
                    <?php echo $request['status']; ?>
                </span>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="mt-4">
            <a href="/deployments" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 text-sm font-medium">
                View all deployment requests →
            </a>
        </div>
    </div>

    <!-- Recent Servers -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Recently Added Servers</h3>
        <div class="space-y-3">
            <?php if (empty($recentServers)): ?>
            <p class="text-sm text-gray-500 dark:text-gray-400">No recently added servers</p>
            <?php else: ?>
            <?php foreach ($recentServers as $server): ?>
            <div class="flex items-center justify-between py-2 border-b dark:border-gray-700 last:border-b-0">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($server['server_name']); ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        By <?php echo htmlspecialchars($server['creator_name']); ?> • 
                        <?php echo date('M j, Y', strtotime($server['created_at'])); ?>
                    </p>
                </div>
                <span class="px-2 py-1 text-xs font-medium rounded-full 
                    <?php echo $server['asset_class'] === 'CJ' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : ''; ?>
                    <?php echo $server['asset_class'] === 'C1' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : ''; ?>
                    <?php echo $server['asset_class'] === 'C2' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : ''; ?>
                    <?php echo $server['asset_class'] === 'NC' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ''; ?>
                ">
                    <?php echo $server['asset_class']; ?>
                </span>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="mt-4">
            <a href="/servers" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300 text-sm font-medium">
                View all servers →
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'app/views/layout.php';
?>