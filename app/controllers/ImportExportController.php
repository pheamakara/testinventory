<?php
// app/controllers/ImportExportController.php
class ImportExportController extends Controller {
    public function importServers() {
        $this->requireAuth();
        $this->requireRole('Editor');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['csv_file']['tmp_name'];
                $handle = fopen($file, 'r');
                
                // Skip header row
                $header = fgetcsv($handle);
                
                $successCount = 0;
                $errorCount = 0;
                $errors = [];
                
                while (($data = fgetcsv($handle)) !== FALSE) {
                    try {
                        // Map CSV columns to database fields
                        $serverData = $this->mapCsvToServer($data, $header);
                        $serverData['created_by'] = $_SESSION['user_id'];
                        
                        // Insert server
                        $columns = implode(', ', array_keys($serverData));
                        $placeholders = implode(', ', array_fill(0, count($serverData), '?'));
                        
                        $stmt = $this->db->prepare("INSERT INTO servers ($columns) VALUES ($placeholders)");
                        $stmt->execute(array_values($serverData));
                        
                        $successCount++;
                    } catch (Exception $e) {
                        $errorCount++;
                        $errors[] = "Row " . ($successCount + $errorCount) . ": " . $e->getMessage();
                    }
                }
                
                fclose($handle);
                
                $_SESSION['import_result'] = [
                    'success' => $successCount,
                    'error' => $errorCount,
                    'errors' => $errors
                ];
                
                $this->redirect('/servers/import');
            }
        }
        
        $result = $_SESSION['import_result'] ?? null;
        unset($_SESSION['import_result']);
        
        $this->view('servers/import', ['result' => $result]);
    }
    
    public function exportServers() {
        $this->requireAuth();
        
        // Get all servers
        $stmt = $this->db->prepare("SELECT * FROM servers");
        $stmt->execute();
        $servers = $stmt->fetchAll();
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="servers_export_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Write header row
        $header = [
            'No', 'In_CJ_Asset', 'Server Name', 'Type', 'Environment', 'Site', 'VM Cluster',
            'Private IP', 'Secondary IP', 'Public IP/Static NAT', 'Server functions', 'Application Name',
            'Server Model/Type', 'CPU/vCPU', 'Memory', 'HDD', 'OS Family', 'Distribution/Edition',
            'Version', 'Server Architecture', 'Asset Type', 'Service Category',
            'Business Service/Systems/URL', 'System End User', 'Server PIC',
            'App/DB Team PIC', 'DB/APP PIC', 'External Vendor (Email)', 'OS License Type',
            'Checklist', 'Deployment Date', 'Confidential', 'Integrity', 'Availability',
            'Finance', 'Reputation', 'Privacy', 'Regulatory', 'Service', 'Sec Scores',
            'Bus Score', 'Asset Class', 'Min CJ', 'Patch PIC', 'Patch Type',
            'Patch Schedule (Day/Week)', 'Patch Time', 'Patch Frequency', 'Reboot Policy',
            'Custom Group (Patch Manager)', 'Auto Snapshot', 'Last Patch Deployed Date',
            'Target Distribution/Edition', 'Target Version', 'Upgrade/Migrate PIC',
            'Review Status'
        ];
        
        fputcsv($output, $header);
        
        // Write data rows
        foreach ($servers as $index => $server) {
            $row = [
                $index + 1,
                $server['in_cj_asset'] ? 'Yes' : 'No',
                $server['server_name'],
                $server['type'],
                $server['environment'],
                $server['site'],
                $server['vm_cluster'],
                $server['private_ip'],
                $server['secondary_ip'],
                $server['public_ip'],
                $server['server_functions'],
                $server['application_name'],
                $server['server_model'],
                $server['cpu_vcpu'],
                $server['memory'],
                $server['hdd'],
                $server['os_family'],
                $server['distribution_edition'],
                $server['version'],
                $server['server_architecture'],
                $server['asset_type'],
                $server['service_category'],
                $server['business_service'],
                $server['system_end_user'],
                $server['server_pic'],
                $server['app_db_team_pic'],
                $server['db_app_pic'],
                $server['external_vendor_email'],
                $server['os_license_type'],
                $server['checklist'],
                $server['deployment_date'],
                $server['confidential'],
                $server['integrity'],
                $server['availability'],
                $server['finance'] ? 'Yes' : 'No',
                $server['reputation'] ? 'Yes' : 'No',
                $server['privacy'] ? 'Yes' : 'No',
                $server['regulatory'] ? 'Yes' : 'No',
                $server['service'] ? 'Yes' : 'No',
                $server['sec_score'],
                $server['bus_score'],
                $server['asset_class'],
                $server['min_cj'],
                $server['patch_pic'],
                $server['patch_type'],
                $server['patch_schedule'],
                $server['patch_time'],
                $server['patch_frequency'],
                $server['reboot_policy'],
                $server['custom_group'],
                $server['auto_snapshot'] ? 'Yes' : 'No',
                $server['last_patch_deployed_date'],
                $server['target_distribution_edition'],
                $server['target_version'],
                $server['upgrade_migrate_pic'],
                $server['review_status']
            ];
            
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    private function mapCsvToServer($data, $header) {
        $mapping = [
            'In_CJ_Asset' => ['field' => 'in_cj_asset', 'type' => 'bool'],
            'Server Name' => ['field' => 'server_name', 'type' => 'string'],
            'Type' => ['field' => 'type', 'type' => 'string'],
            'Environment' => ['field' => 'environment', 'type' => 'string'],
            'Site' => ['field' => 'site', 'type' => 'string'],
            'VM Cluster' => ['field' => 'vm_cluster', 'type' => 'string'],
            'Private IP' => ['field' => 'private_ip', 'type' => 'string'],
            'Secondary IP' => ['field' => 'secondary_ip', 'type' => 'string'],
            'Public IP/Static NAT' => ['field' => 'public_ip', 'type' => 'string'],
            'Server functions' => ['field' => 'server_functions', 'type' => 'string'],
            'Application Name' => ['field' => 'application_name', 'type' => 'string'],
            'Server Model/Type' => ['field' => 'server_model', 'type' => 'string'],
            'CPU/vCPU' => ['field' => 'cpu_vcpu', 'type' => 'string'],
            'Memory' => ['field' => 'memory', 'type' => 'string'],
            'HDD' => ['field' => 'hdd', 'type' => 'string'],
            'OS Family' => ['field' => 'os_family', 'type' => 'string'],
            'Distribution/Edition' => ['field' => 'distribution_edition', 'type' => 'string'],
            'Version' => ['field' => 'version', 'type' => 'string'],
            'Server Architecture' => ['field' => 'server_architecture', 'type' => 'string'],
            'Asset Type' => ['field' => 'asset_type', 'type' => 'string'],
            'Service Category' => ['field' => 'service_category', 'type' => 'string'],
            'Business Service/Systems/URL' => ['field' => 'business_service', 'type' => 'string'],
            'System End User' => ['field' => 'system_end_user', 'type' => 'string'],
            'Server PIC' => ['field' => 'server_pic', 'type' => 'string'],
            'App/DB Team PIC' => ['field' => 'app_db_team_pic', 'type' => 'string'],
            'DB/APP PIC' => ['field' => 'db_app_pic', 'type' => 'string'],
            'External Vendor (Email)' => ['field' => 'external_vendor_email', 'type' => 'string'],
            'OS License Type' => ['field' => 'os_license_type', 'type' => 'string'],
            'Checklist' => ['field' => 'checklist', 'type' => 'string'],
            'Deployment Date' => ['field' => 'deployment_date', 'type' => 'date'],
            'Confidential' => ['field' => 'confidential', 'type' => 'int'],
            'Integrity' => ['field' => 'integrity', 'type' => 'int'],
            'Availability' => ['field' => 'availability', 'type' => 'int'],
            'Finance' => ['field' => 'finance', 'type' => 'bool'],
            'Reputation' => ['field' => 'reputation', 'type' => 'bool'],
            'Privacy' => ['field' => 'privacy', 'type' => 'bool'],
            'Regulatory' => ['field' => 'regulatory', 'type' => 'bool'],
            'Service' => ['field' => 'service', 'type' => 'bool'],
            'Min CJ' => ['field' => 'min_cj', 'type' => 'string'],
            'Patch PIC' => ['field' => 'patch_pic', 'type' => 'string'],
            'Patch Type' => ['field' => 'patch_type', 'type' => 'string'],
            'Patch Schedule (Day/Week)' => ['field' => 'patch_schedule', 'type' => 'string'],
            'Patch Time' => ['field' => 'patch_time', 'type' => 'time'],
            'Patch Frequency' => ['field' => 'patch_frequency', 'type' => 'string'],
            'Reboot Policy' => ['field' => 'reboot_policy', 'type' => 'string'],
            'Custom Group (Patch Manager)' => ['field' => 'custom_group', 'type' => 'string'],
            'Auto Snapshot' => ['field' => 'auto_snapshot', 'type' => 'bool'],
            'Last Patch Deployed Date' => ['field' => 'last_patch_deployed_date', 'type' => 'date'],
            'Target Distribution/Edition' => ['field' => 'target_distribution_edition', 'type' => 'string'],
            'Target Version' => ['field' => 'target_version', 'type' => 'string'],
            'Upgrade/Migrate PIC' => ['field' => 'upgrade_migrate_pic', 'type' => 'string'],
            'Review Status' => ['field' => 'review_status', 'type' => 'string']
        ];
        
        $serverData = [];
        
        foreach ($header as $index => $columnName) {
            if (isset($mapping[$columnName]) && isset($data[$index])) {
                $field = $mapping[$columnName]['field'];
                $type = $mapping[$columnName]['type'];
                $value = trim($data[$index]);
                
                switch ($type) {
                    case 'bool':
                        $serverData[$field] = in_array(strtolower($value), ['yes', 'true', '1', 'y']);
                        break;
                    case 'int':
                        $serverData[$field] = (int)$value;
                        break;
                    case 'date':
                        $serverData[$field] = !empty($value) ? $value : null;
                        break;
                    case 'time':
                        $serverData[$field] = !empty($value) ? $value : null;
                        break;
                    default:
                        $serverData[$field] = $value;
                }
            }
        }
        
        return $serverData;
    }
}