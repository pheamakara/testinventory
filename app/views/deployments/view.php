<!-- app/views/deployments/view.php -->
<?php 
ob_start();
$pageTitle = "Deployment Request #" . $request['id'];
$csrfToken = $this->csrfToken();
?>

<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Deployment Request #<?php echo $request['id']; ?></h2>
    
    <div class="flex space-x-2">
        <span class="px-3 py-1 rounded-full text-sm font-medium 
            <?php echo $request['status'] === 'Draft' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : ''; ?>
            <?php echo $request['status'] === 'Pending Security' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' : ''; ?>
            <?php echo $request['status'] === 'Pending Manager' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : ''; ?>
            <?php echo $request['status'] === 'Approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : ''; ?>
            <?php echo $request['status'] === 'Rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : ''; ?>
        ">
            <?php echo $request['status']; ?>
        </span>
        
        <?php if ($request['status'] === 'Approved'): ?>
        <button class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm" onclick="window.print()">
            <i class="fas fa-print mr-1"></i> Print PDF
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Progress Bar -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
    <div class="flex justify-between items-center mb-2">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Checklist Completion</span>
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo $completionPercentage; ?>%</span>
    </div>
    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
        <div class="bg-green-600 h-2 rounded-full" style="width: <?php echo $completionPercentage; ?>%"></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Request Details -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 lg:col-span-1">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 border-b pb-2">Request Details</h3>
        
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Host IP</label>
                <p class="text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($request['host_ip']); ?></p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Host Name</label>
                <p class="text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($request['host_name']); ?></p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Rack Name</label>
                <p class="text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($request['rack_name']); ?></p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Server Type</label>
                <p class="text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($request['server_type']); ?></p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Environment</label>
                <p class="text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($request['environment']); ?></p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">VM Cluster</label>
                <p class="text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($request['vm_cluster']); ?></p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Site</label>
                <p class="text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($request['site']); ?></p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Asset Criticality</label>
                <p class="text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($request['asset_criticality']); ?></p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Requested By</label>
                <p class="text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($request['requester_name']); ?></p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Created At</label>
                <p class="text-sm text-gray-900 dark:text-white"><?php echo date('Y-m-d H:i', strtotime($request['created_at'])); ?></p>
            </div>
        </div>
        
        <!-- Action buttons -->
        <div class="mt-6 pt-4 border-t dark:border-gray-700">
            <?php if ($request['status'] === 'Draft' && $request['requested_by'] == $_SESSION['user_id']): ?>
            <form method="POST" action="/deployments/submit/<?php echo $request['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-md">
                    Submit for Approval
                </button>
            </form>
            <?php endif; ?>
            
            <?php if (($request['status'] === 'Pending Security' && ($this->hasRole('Security') || $this->hasRole('Admin'))) || 
                      ($request['status'] === 'Pending Manager' && ($this->hasRole('Manager') || $this->hasRole('Admin')))): ?>
            <div class="mt-4">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Approve/Reject</h4>
                <form method="POST" action="/deployments/approve/<?php echo $request['id']; ?>/<?php echo $request['status'] === 'Pending Security' ? 'security' : 'manager'; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <textarea name="comment" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white mb-2" placeholder="Comments (required)"></textarea>
                    <div class="flex space-x-2">
                        <button type="submit" name="decision" value="Approved" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md">Approve</button>
                        <button type="submit" name="decision" value="Rejected" class="flex-1 bg-red-600 hover:red-700 text-white py-2 px-4 rounded-md">Reject</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Checklist -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 lg:col-span-2">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 border-b pb-2">Deployment Checklist</h3>
        
        <?php if ($request['status'] === 'Draft' && $request['requested_by'] == $_SESSION['user_id']): ?>
        <form method="POST" action="/deployments/update-checklist/<?php echo $request['id']; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        <?php endif; ?>
        
        <div class="space-y-4">
            <?php 
            $currentCategory = '';
            foreach ($checklist as $item): 
                if ($item['category'] !== $currentCategory): 
                    $currentCategory = $item['category'];
            ?>
            <h4 class="text-md font-medium text-gray-900 dark:text-white mt-6 mb-3 border-b pb-1"><?php echo $currentCategory; ?> Deployment Checklist</h4>
            <?php endif; ?>
            
            <div class="flex items-start">
                <div class="flex-shrink-0 mr-3">
                    <?php if ($request['status'] === 'Draft' && $request['requested_by'] == $_SESSION['user_id']): ?>
                    <select name="checklist[<?php echo $item['id']; ?>][status]" class="text-sm border border-gray-300 dark:border-gray-600 rounded shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                        <option value="Not Completed" <?php echo $item['status'] === 'Not Completed' ? 'selected' : ''; ?>>Not Completed</option>
                        <option value="Completed" <?php echo $item['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="N/A" <?php echo $item['status'] === 'N/A' ? 'selected' : ''; ?>>N/A</option>
                    </select>
                    <?php else: ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        <?php echo $item['status'] === 'Completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : ''; ?>
                        <?php echo $item['status'] === 'Not Completed' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : ''; ?>
                        <?php echo $item['status'] === 'N/A' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : ''; ?>
                    ">
                        <?php echo $item['status']; ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <div class="flex-1">
                    <p class="text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($item['description']); ?></p>
                    
                    <?php if (!empty($item['comment'])): ?>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Comment: <?php echo htmlspecialchars($item['comment']); ?></p>
                    <?php endif; ?>
                    
                    <?php if ($item['status'] === 'Completed' && !empty($item['performed_by'])): ?>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Completed by <?php echo htmlspecialchars($item['performed_by']); ?> 
                        at <?php echo date('Y-m-d H:i', strtotime($item['completed_at'])); ?>
                    </p>
                    <?php endif; ?>
                    
                    <?php if ($request['status'] === 'Draft' && $request['requested_by'] == $_SESSION['user_id']): ?>
                    <textarea name="checklist[<?php echo $item['id']; ?>][comment]" rows="2" class="w-full mt-2 px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-sm" placeholder="Comments"><?php echo htmlspecialchars($item['comment'] ?? ''); ?></textarea>
                    <?php elseif (($this->hasRole('Security') || $this->hasRole('Manager') || $this->hasRole('Admin')) && $request['status'] !== 'Draft'): ?>
                    <textarea name="checklist[<?php echo $item['id']; ?>][comment]" rows="2" class="w-full mt-2 px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-sm" placeholder="Add comment"><?php echo htmlspecialchars($item['comment'] ?? ''); ?></textarea>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($request['status'] === 'Draft' && $request['requested_by'] == $_SESSION['user_id']): ?>
        <div class="mt-6 pt-4 border-t dark:border-gray-700">
            <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-md">
                Save Checklist
            </button>
        </div>
        </form>
        <?php elseif (($this->hasRole('Security') || $this->hasRole('Manager') || $this->hasRole('Admin')) && $request['status'] !== 'Draft'): ?>
        <div class="mt-6 pt-4 border-t dark:border-gray-700">
            <form method="POST" action="/deployments/update-checklist/<?php echo $request['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-md">
                    Save Comments
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Approval History -->
<?php if (!empty($approvals)): ?>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 border-b pb-2">Approval History</h3>
    
    <div class="space-y-4">
        <?php foreach ($approvals as $approval): ?>
        <div class="flex items-start">
            <div class="flex-shrink-0 mr-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                    <?php echo $approval['decision'] === 'Approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'; ?>
                ">
                    <?php echo $approval['decision']; ?>
                </span>
            </div>
            
            <div class="flex-1">
                <p class="text-sm text-gray-900 dark:text-white">
                    <?php echo htmlspecialchars($approval['approver_role']); ?>: 
                    <?php echo htmlspecialchars($approval['approver_name']); ?>
                </p>
                
                <?php if (!empty($approval['comment'])): ?>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Comment: <?php echo htmlspecialchars($approval['comment']); ?></p>
                <?php endif; ?>
                
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    <?php echo date('Y-m-d H:i', strtotime($approval['created_at'])); ?>
                </p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include 'app/views/layout.php';
?>