-- sql/schema.sql
CREATE DATABASE IF NOT EXISTS server_management;
USE server_management;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Editor', 'Viewer', 'Security', 'Manager') DEFAULT 'Viewer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Servers table
CREATE TABLE servers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    in_cj_asset BOOLEAN DEFAULT FALSE,
    server_name VARCHAR(255) UNIQUE NOT NULL,
    type VARCHAR(50),
    environment VARCHAR(50),
    site VARCHAR(50),
    vm_cluster VARCHAR(100),
    private_ip VARCHAR(15),
    secondary_ip VARCHAR(15),
    public_ip VARCHAR(15),
    server_functions TEXT,
    application_name VARCHAR(255),
    server_model VARCHAR(100),
    cpu_vcpu VARCHAR(50),
    memory VARCHAR(50),
    hdd VARCHAR(50),
    os_family VARCHAR(50),
    distribution_edition VARCHAR(100),
    version VARCHAR(50),
    server_architecture VARCHAR(50),
    asset_type VARCHAR(50),
    service_category VARCHAR(100),
    business_service TEXT,
    system_end_user VARCHAR(100),
    server_pic VARCHAR(100),
    app_db_team_pic VARCHAR(100),
    db_app_pic VARCHAR(100),
    external_vendor_email VARCHAR(100),
    os_license_type VARCHAR(50),
    checklist TEXT,
    deployment_date DATE,
    confidential TINYINT CHECK (confidential BETWEEN 1 AND 5),
    integrity TINYINT CHECK (integrity BETWEEN 1 AND 5),
    availability TINYINT CHECK (availability BETWEEN 1 AND 5),
    finance BOOLEAN DEFAULT FALSE,
    reputation BOOLEAN DEFAULT FALSE,
    privacy BOOLEAN DEFAULT FALSE,
    regulatory BOOLEAN DEFAULT FALSE,
    service BOOLEAN DEFAULT FALSE,
    sec_score INT GENERATED ALWAYS AS (confidential + integrity + availability) STORED,
    bus_score INT GENERATED ALWAYS AS (
        finance + reputation + privacy + regulatory + service
    ) STORED,
    asset_class VARCHAR(10) GENERATED ALWAYS AS (
        CASE
            WHEN (confidential + integrity + availability) >= 12 OR (finance + reputation + privacy + regulatory + service) >= 3 THEN 'CJ'
            WHEN (confidential + integrity + availability) >= 8 OR (finance + reputation + privacy + regulatory + service) = 2 THEN 'C1'
            WHEN (confidential + integrity + availability) >= 5 AND (finance + reputation + privacy + regulatory + service) = 1 THEN 'C2'
            WHEN (confidential + integrity + availability) >= 1 AND (finance + reputation + privacy + regulatory + service) <= 1 THEN 'NC'
            ELSE NULL
        END
    ) STORED,
    min_cj VARCHAR(50),
    patch_pic VARCHAR(100),
    patch_type VARCHAR(50),
    patch_schedule VARCHAR(50),
    patch_time TIME,
    patch_frequency VARCHAR(50),
    reboot_policy VARCHAR(100),
    custom_group VARCHAR(100),
    auto_snapshot BOOLEAN DEFAULT FALSE,
    last_patch_deployed_date DATE,
    target_distribution_edition VARCHAR(100),
    target_version VARCHAR(50),
    upgrade_migrate_pic VARCHAR(100),
    review_status VARCHAR(50),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Deployment requests table
CREATE TABLE deployment_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    host_ip VARCHAR(15),
    host_name VARCHAR(255),
    rack_name VARCHAR(100),
    server_type ENUM('Physical', 'Virtual'),
    environment ENUM('Production', 'Staging'),
    vm_cluster ENUM('HQ VM Cluster', 'TKK VM Cluster', 'TKK VDI Cluster', 'TKK VM Staging Cluster', 'Hyper-V Cluster', 'Oracle Cluster'),
    site ENUM('HQ', 'TKK', 'Nehru'),
    asset_criticality ENUM('CJ1', 'CJ2', 'CJ3', 'NC'),
    status ENUM('Draft', 'Pending Security', 'Pending Manager', 'Approved', 'Rejected') DEFAULT 'Draft',
    requested_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (requested_by) REFERENCES users(id)
);

-- Checklist items table
CREATE TABLE checklist_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category ENUM('Common', 'Virtual', 'Physical'),
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Deployment checklist responses
CREATE TABLE deployment_checklists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deployment_request_id INT,
    checklist_item_id INT,
    status ENUM('Completed', 'Not Completed', 'N/A') DEFAULT 'Not Completed',
    comment TEXT,
    performed_by_user_id INT,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (deployment_request_id) REFERENCES deployment_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (checklist_item_id) REFERENCES checklist_items(id),
    FOREIGN KEY (performed_by_user_id) REFERENCES users(id)
);

-- Approval workflow table
CREATE TABLE deployment_approvals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deployment_request_id INT,
    approver_role ENUM('Security', 'Manager'),
    approver_user_id INT,
    decision ENUM('Approved', 'Rejected'),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deployment_request_id) REFERENCES deployment_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (approver_user_id) REFERENCES users(id)
);

-- Audit log table
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT,
    old_values TEXT,
    new_values TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert default checklist items
INSERT INTO checklist_items (category, description) VALUES
('Common', 'Server name unique and follows naming policy'),
('Common', 'Update Server Assessment list (add to server asset list)'),
('Common', 'Add server to Central Linux (Linux OS) → https://central-linux.smart.com.kh/'),
('Common', 'Deploy CrowdStrike (via Central Linux)'),
('Common', 'Deploy and add to Endpoint Central → https://server.smart.com.kh:8383 (via Central Linux)'),
('Common', 'Create users pam.scan & pam.func (via Central Linux)'),
('Common', 'Update IP/Subnet/Hostname in IPAM'),
('Common', 'Configure PAM sudoers (passwordless)'),
('Common', 'Register & deploy CheckMK agent (via Central Linux)'),
('Common', 'Update monitoring systems (Cacti, CheckMK, SolarWinds, eSight)'),
('Common', 'Update switch ports (network, SAN) & email alerts'),
('Common', 'Update related passwords in Secret → https://secret.smart.com.kh/'),
('Common', 'Add server to Syslog → https://log.smart.com.kh/'),
('Common', 'Backup status (Completed / Not Completed)'),
('Common', 'For non-domain servers: configure NTP (ntp.smart.com.kh, ntp2.smart.com.kh)'),
('Common', 'Enable VM encryption (if VM)'),
('Common', 'Comply with MBSS security policy'),
('Common', 'Complete OS security scan & remediate vulnerabilities'),
('Virtual', 'Select correct guest OS and version'),
('Virtual', 'Use compatible NIC (VMXNET3 recommended)'),
('Virtual', 'Select proper VLAN (portgroup); create if needed'),
('Virtual', 'Assign CPU/RAM per service recommendations'),
('Virtual', 'Choose datastore per space, redundancy, and DR needs'),
('Virtual', 'Install VMware Tools'),
('Virtual', 'NIC: enable "Connect at Power On"'),
('Virtual', 'Enable CPU Hot Add and Memory Hot Plug'),
('Virtual', 'Remove unused devices (floppy, ISO, etc.)'),
('Physical', 'Install server properly in rack'),
('Physical', 'Connect to two power sources with correct power cords'),
('Physical', 'Label power cords, network & SAN cables, and server per naming convention'),
('Physical', 'Enable lock screen, screensaver, session timeout, CMOS password'),
('Physical', 'Disable boot from USB/Network/CD after install'),
('Physical', 'Update SAN & network switch port descriptions'),
('Physical', 'Monitor hardware via iLO/iMana/iDRAC'),
('Physical', 'Remove unused peripherals after installation'),
('Physical', 'Ensure server, rack, and server room are physically locked'),
('Physical', 'Attach asset tag & update GLPI → https://asset.smart.com.kh/'),
('Physical', 'Update storage layout diagram'),
('Physical', 'Update rack diagram list'),
('Physical', 'Update physical server diagram');

-- Insert default admin user
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin');