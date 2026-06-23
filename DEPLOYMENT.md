# Deployment Guide

This guide covers two methods to deploy the PHP application to a remote Docker server.

---

## Prerequisites

- Docker and Docker Compose installed on the remote server
- SSH access to the remote server
- Docker installed locally

---

## Method 1: Docker Context (TCP)

Docker contexts allow you to manage remote Docker engines directly from your local CLI.

### 1.1 Configure Remote Docker Daemon

On the remote server, edit `/etc/docker/daemon.json`:

```json
{
  "hosts": [
    "unix:///var/run/docker.sock",
    "tcp://0.0.0.0:2375"
  ]
}
```

Restart Docker:

```bash
sudo systemctl restart docker
```

### 1.2 Create Docker Context Locally

```bash
# Create context pointing to remote Docker daemon
docker context create remote --docker "host=tcp://<REMOTE_IP>:2375" --description "Remote Docker server"

# Verify connection
docker --context remote ps
```

### 1.3 Deploy Application

```bash
# Build and start containers on remote
docker --context remote compose up -d --build

# Check status
docker --context remote ps
```

### 1.4 Database Transfer (Optional)

Export from local and restore on remote:

```bash
# Export local database
docker exec <local_db_container> bash -c 'mysqldump -u root -p$(cat /run/secrets/db_root_password) savings_db 2>/dev/null' > db_backup.sql

# Copy backup to remote container
docker --context remote cp db_backup.sql <remote_db_container>:/tmp/db_backup.sql

# Restore on remote
docker --context remote exec <remote_db_container> bash -c 'mysql -u root -p$(cat /run/secrets/db_root_password) savings_db < /tmp/db_backup.sql'
```

### 1.5 Useful Commands

```bash
# Set as default context
docker context use remote

# Switch back to local
docker context use default

# Remove context
docker context rm remote
```

---

## Method 2: SSH-based Deployment

Use SSH to connect and run Docker commands directly on the remote server.

### 2.1 Install SSH Tools

On Windows, install `sshpass` via WSL for non-interactive password auth:

```bash
wsl -u root -e bash -c "apt-get update && apt-get install -y sshpass"
```

### 2.2 Copy Project Files

```bash
# Create project directory on remote
sshpass -p '<PASSWORD>' ssh -o StrictHostKeyChecking=no root@<REMOTE_IP> "mkdir -p /opt/oc-test-php"

# Copy files via SCP
sshpass -p '<PASSWORD>' scp -o StrictHostKeyChecking=no -r ./* root@<REMOTE_IP>:/opt/oc-test-php/
```

### 2.3 Deploy on Remote

```bash
# Build and start containers
sshpass -p '<PASSWORD>' ssh -o StrictHostKeyChecking=no root@<REMOTE_IP> "cd /opt/oc-test-php && docker compose up -d --build"

# Check container status
sshpass -p '<PASSWORD>' ssh -o StrictHostKeyChecking=no root@<REMOTE_IP> "docker ps"
```

### 2.4 Windows PowerShell (Posh-SSH)

Alternatively, use the Posh-SSH PowerShell module:

```powershell
# Install module
Install-Module -Name Posh-SSH -Force -Scope CurrentUser

# Connect and run commands
$password = 'your_password'
$secPass = ConvertTo-SecureString $password -AsPlainText -Force
$cred = New-Object System.Management.Automation.PSCredential("root", $secPass)
$session = New-SSHSession -ComputerName "<REMOTE_IP>" -Credential $cred -AcceptKey

# Run remote command
$result = Invoke-SSHCommand -SessionId $session.SessionId -Command "docker ps"
Write-Output $result.Output

# Disconnect
Remove-SSHSession -SessionId $session.SessionId
```

---

## Database Schema Fix

If you encounter `Unknown column 'username'` errors, the schema needs updating:

```sql
ALTER TABLE users 
ADD COLUMN username VARCHAR(100) DEFAULT NULL AFTER lastname,
ADD COLUMN role TINYINT DEFAULT 0 AFTER multiplier,
ADD COLUMN password VARCHAR(255) NOT NULL AFTER role;
```

Run inside the DB container:

```bash
docker exec <db_container> bash -c 'mysql -u root -p$(cat /run/secrets/db_root_password) savings_db -e "ALTER TABLE users ADD COLUMN username VARCHAR(100) DEFAULT NULL AFTER lastname, ADD COLUMN role TINYINT DEFAULT 0 AFTER multiplier, ADD COLUMN password VARCHAR(255) NOT NULL AFTER role;"'
```

---

## Application Access

After deployment:

| Service | URL |
|---------|-----|
| App | `http://<REMOTE_IP>:8490` |
| MySQL | `<REMOTE_IP>:3306` |
| Portainer | `https://<REMOTE_IP>:9443` |

---

## Comparison

| Feature | Docker Context | SSH |
|---------|---------------|-----|
| Setup complexity | Medium | Low |
| Security | Exposes Docker TCP | Uses SSH encryption |
| Speed | Faster | Slower (SSH overhead) |
| File transfer | Not built-in | SCP/rsync |
| Use case | Dev/CI environments | Production |

---

## Security Notes

- **Docker Context (TCP)**: Port 2375 is unencrypted. Use TLS (port 2376) in production.
- **SSH**: More secure, but requires password handling or SSH keys.
- Consider using SSH keys instead of passwords for production environments.
