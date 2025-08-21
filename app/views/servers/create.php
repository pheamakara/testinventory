<!-- app/views/servers/create.php -->
<?php 
ob_start();
$pageTitle = "Add Server";
$csrfToken = $this->csrfToken();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Add New Server</h2>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <form method="POST" class="p-6">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 border-b pb-2">Basic Information</h3>
                
                <div class="mb-4">
                    <label for="server_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Server Name *</label>
                    <input type="text" id="server_name" name="server_name" required 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div class="mb-4">
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                    <select id="type" name="type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Type</option>
                        <option value="Physical">Physical</option>
                        <option value="Virtual">Virtual</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="environment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Environment</label>
                    <select id="environment" name="environment" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Environment</option>
                        <option value="Production">Production</option>
                        <option value="Staging">Staging</option>
                        <option value="Development">Development</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="site" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Site</label>
                    <select id="site" name="site" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Site</option>
                        <option value="HQ">HQ</option>
                        <option value="TKK">TKK</option>
                        <option value="Nehru">Nehru</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="vm_cluster" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">VM Cluster</label>
                    <input type="text" id="vm_cluster" name="vm_cluster" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 border-b pb-2">Network Information</h3>
                
                <div class="mb-4">
                    <label for="private_ip" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Private IP</label>
                    <input type="text" id="private_ip" name="private_ip" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div class="mb-4">
                    <label for="secondary_ip" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Secondary IP</label>
                    <input type="text" id="secondary_ip" name="secondary_ip" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div class="mb-4">
                    <label for="public_ip" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Public IP/Static NAT</label>
                    <input type="text" id="public_ip" name="public_ip" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div class="mb-4">
                    <label for="server_functions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Server Functions</label>
                    <textarea id="server_functions" name="server_functions" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"></textarea>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 border-b pb-2">Application Information</h3>
                
                <div class="mb-4">
                    <label for="application_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Application Name</label>
                    <input type="text" id="application_name" name="application_name" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div class="mb-4">
                    <label for="business_service" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Business Service/Systems/URL</label>
                    <textarea id="business_service" name="business_service" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="system_end_user" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">System End User</label>
                    <input type="text" id="system_end_user" name="system_end_user" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>
        </div>
        
        <!-- Risk Scoring Section -->
        <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 border-b pb-2">Risk Scoring</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <div>
                    <label for="confidential" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confidential (1-5)</label>
                    <input type="number" id="confidential" name="confidential" min="1" max="5" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label for="integrity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Integrity (1-5)</label>
                    <input type="number" id="integrity" name="integrity" min="1" max="5" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label for="availability" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Availability (1-5)</label>
                    <input type="number" id="availability" name="availability" min="1" max="5" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div class="col-span-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Business Impact</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="finance" value="1" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Finance</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="reputation" value="1" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Reputation</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">&nbsp;</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="privacy" value="1" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Privacy</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="regulatory" value="1" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Regulatory</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="service" value="1" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Service</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-md">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Calculated Scores (Read-only)</h4>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400">Security Score</label>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white" id="sec-score-display">-</div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400">Business Score</label>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white" id="bus-score-display">-</div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-gray-400">Asset Class</label>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white" id="asset-class-display">-</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional Information Section -->
        <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 border-b pb-2">Additional Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="mb-4">
                        <label for="server_model" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Server Model/Type</label>
                        <input type="text" id="server_model" name="server_model" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    
                    <div class="mb-4">
                        <label for="cpu_vcpu" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CPU/vCPU</label>
                        <input type="text" id="cpu_vcpu" name="cpu_vcpu" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    
                    <div class="mb-4">
                        <label for="memory" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Memory</label>
                        <input type="text" id="memory" name="memory" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    
                    <div class="mb-4">
                        <label for="hdd" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">HDD</label>
                        <input type="text" id="hdd" name="hdd" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
                
                <div>
                    <div class="mb-4">
                        <label for="os_family" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">OS Family</label>
                        <input type="text" id="os_family" name="os_family" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    
                    <div class="mb-4">
                        <label for="distribution_edition" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Distribution/Edition</label>
                        <input type="text" id="distribution_edition" name="distribution_edition" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    
                    <div class="mb-4">
                        <label for="version" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Version</label>
                        <input type="text" id="version" name="version" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    
                    <div class="mb-4">
                        <label for="server_architecture" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Server Architecture</label>
                        <input type="text" id="server_architecture" name="server_architecture" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex justify-end">
            <a href="/servers" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500 mr-3">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md">Save Server</button>
        </div>
    </form>
</div>

<script>
    // Calculate risk scores in real-time
    function calculateScores() {
        const confidential = parseInt(document.getElementById('confidential').value) || 0;
        const integrity = parseInt(document.getElementById('integrity').value) || 0;
        const availability = parseInt(document.getElementById('availability').value) || 0;
        
        const finance = document.querySelector('input[name="finance"]').checked ? 1 : 0;
        const reputation = document.querySelector('input[name="reputation"]').checked ? 1 : 0;
        const privacy = document.querySelector('input[name="privacy"]').checked ? 1 : 0;
        const regulatory = document.querySelector('input[name="regulatory"]').checked ? 1 : 0;
        const service = document.querySelector('input[name="service"]').checked ? 1 : 0;
        
        const secScore = confidential + integrity + availability;
        const busScore = finance + reputation + privacy + regulatory + service;
        
        let assetClass = '-';
        if (secScore >= 12 || busScore >= 3) {
            assetClass = 'CJ';
        } else if (secScore >= 8 || busScore === 2) {
            assetClass = 'C1';
        } else if (secScore >= 5 && busScore === 1) {
            assetClass = 'C2';
        } else if (secScore >= 1 && busScore <= 1) {
            assetClass = 'NC';
        }
        
        document.getElementById('sec-score-display').textContent = secScore;
        document.getElementById('bus-score-display').textContent = busScore;
        document.getElementById('asset-class-display').textContent = assetClass;
    }
    
    // Add event listeners to all risk scoring inputs
    document.getElementById('confidential').addEventListener('input', calculateScores);
    document.getElementById('integrity').addEventListener('input', calculateScores);
    document.getElementById('availability').addEventListener('input', calculateScores);
    
    const checkboxes = document.querySelectorAll('input[type="checkbox"][name]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', calculateScores);
    });
    
    // Initial calculation
    calculateScores();
</script>

<?php
$content = ob_get_clean();
include 'app/views/layout.php';
?>