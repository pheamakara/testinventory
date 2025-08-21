-- Add new columns to users table
ALTER TABLE users 
ADD COLUMN is_ldap_user BOOLEAN DEFAULT FALSE,
ADD COLUMN is_active BOOLEAN DEFAULT TRUE,
ADD COLUMN last_login TIMESTAMP NULL;

-- Create system settings table
CREATE TABLE system_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT INTO system_settings (setting_key, setting_value) VALUES
('ldap_enabled', '0'),
('ldap_host', ''),
('ldap_port', '389'),
('ldap_base_dn', ''),
('ldap_bind_dn', ''),
('ldap_bind_password', ''),
('ldap_user_filter', '(uid=%s)'),
('ldap_default_role', 'Viewer'),
('email_enabled', '0'),
('email_host', ''),
('email_port', '587'),
('email_username', ''),
('email_password', ''),
('email_encryption', 'tls'),
('email_from', 'noreply@example.com'),
('email_from_name', 'Server Management System'),
('email_cc', ''),
('email_bcc', ''),
('email_audit_alerts', '1'),
('email_approval_notifications', '1'),
('app_name', 'Server Management System'),
('app_timezone', 'UTC'),
('app_maintenance_mode', '0');