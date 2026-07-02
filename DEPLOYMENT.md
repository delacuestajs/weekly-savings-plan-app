# Deployment Guide

This guide covers deployment methods for the PHP application to a remote Docker server.

---

## Prerequisites

- Docker and Docker Compose installed on the remote server
- SSH access to the remote server
- Docker installed locally

---

## Configuration

### 1. Create Environment File

Copy the example environment file and fill in your values:

```bash
cp .env.example .env
```

Edit `.env` with your actual configuration:

```env
CADDY_DOMAIN=your-domain.com
APP_PORT=9283
DB_HOST=db
DB_NAME=savings_db
DB_USERNAME=root
DB_PASSWORD=your_secure_password
DB_ROOT_PASSWORD=your_root_password
DEFAULT_PASSWORD=abcd1234
APP_VERSION=1.0.1
APP_BUILD_DATE=2026-07-01 00:00:00
```

### 2. Environment Variables Reference

| Variable | Description | Default |
|----------|-------------|---------|
| `CADDY_DOMAIN` | Domain/DDNS for HTTPS | (required) |
| `APP_PORT` | Local LAN access port | `9283` |
| `DB_HOST` | Database hostname | `db` |
| `DB_NAME` | MySQL database name | `savings_db` |
| `DB_USERNAME` | MySQL username | `root` |
| `DB_PASSWORD` | MySQL password | (required) |
| `DB_ROOT_PASSWORD` | MySQL root password | (required) |
| `DEFAULT_PASSWORD` | Default password for new users | `abcd1234` |
| `APP_VERSION` | Application version | `1.0.1` |
| `APP_BUILD_DATE` | Build timestamp | (auto-generated) |

### 3. Timezone Configuration

The application uses `America/Bogota` (UTC-5) as default timezone. To change it:

1. Edit `locale.php` and update the timezone:
   ```php
   date_default_timezone_set('America/New_York');
   ```

2. Common timezone identifiers:
   | Timezone | Identifier |
   |----------|------------|
   | UTC-5 (Colombia, Peru) | `America/Bogota` |
   | UTC-5 (US Eastern) | `America/New_York` |
   | UTC-6 (US Central) | `America/Chicago` |
   | UTC-7 (US Mountain) | `America/Denver` |
   | UTC-8 (US Pacific) | `America/Los_Angeles` |
   | UTC+0 (London) | `Europe/London` |
   | UTC+1 (Paris, Madrid) | `Europe/Paris` |
   | UTC+2 (Berlin, Rome) | `Europe/Berlin` |

   Full list: https://www.php.net/manual/en/timezones.php

---

## Method 1: Docker Context (Recommended)

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

### 1.3 Setup .env on Remote Server

Copy your `.env` file to the remote server:

```bash
scp .env root@<REMOTE_IP>:/opt/your-project/
```

### 1.4 Deploy Application

```bash
# Build and start containers on remote
docker --context remote compose --env-file .env up -d --build

# Check status
docker --context remote ps
```

### 1.5 Useful Commands

```bash
# View logs
docker --context remote compose logs -f

# Restart a service
docker --context remote restart <service_name>

# Execute command in container
docker --context remote exec -it <container_name> bash

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
sshpass -p '<PASSWORD>' ssh -o StrictHostKeyChecking=no root@<REMOTE_IP> "mkdir -p /opt/your-project"

# Copy files via SCP (including .env)
sshpass -p '<PASSWORD>' scp -o StrictHostKeyChecking=no -r ./* .env root@<REMOTE_IP>:/opt/your-project/
```

### 2.3 Deploy on Remote

```bash
# Build and start containers
sshpass -p '<PASSWORD>' ssh -o StrictHostKeyChecking=no root@<REMOTE_IP> "cd /opt/your-project && docker compose up -d --build"

# Check container status
sshpass -p '<PASSWORD>' ssh -o StrictHostKeyChecking=no root@<REMOTE_IP> "docker ps"
```

### 2.4 Windows PowerShell

Using environment variables from `.env`:

```powershell
# Load environment variables
Get-Content .env | ForEach-Object {
    if ($_ -match '^([^#][^=]+)=(.*)$') {
        [Environment]::SetEnvironmentVariable($matches[1].Trim(), $matches[2].Trim(), "Process")
    }
}

# Deploy via WSL
wsl -e bash -c "sshpass -p '$env:SSH_PASS' ssh -o StrictHostKeyChecking=no $env:SSH_USER@$env:SSH_HOST 'cd $env:SSH_DIR && docker compose up -d --build'"
```

---

## HTTPS with Caddy (Reverse Proxy)

### Architecture

```
Internet → Router (80/443) → Caddy Container → App Container (80)
```

Caddy automatically obtains and renews Let's Encrypt certificates.

### Prerequisites

- Domain or DDNS hostname (set in `CADDY_DOMAIN` env var)
- Router forwarding ports 80 and 443 to server

### Files

**`.env`**:
```env
CADDY_DOMAIN=your-domain.com
```

**`caddy/Caddyfile`**:
```
{$DOMAIN_NAME} {
    reverse_proxy app:80
}
```

**`docker-compose.yml`** (Caddy service):
```yaml
services:
  caddy:
    image: caddy:2-alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./caddy/Caddyfile:/etc/caddy/Caddyfile
      - caddy-data:/data
      - caddy-config:/config
    environment:
      - DOMAIN_NAME=${CADDY_DOMAIN}
    depends_on:
      - app
    networks:
      - app-network

volumes:
  caddy-data:
  caddy-config:
```

### Deploy HTTPS

```bash
docker --context remote compose --env-file .env up -d --build
```

### Verify

- `https://your-domain.com` - App with valid SSL certificate
- `http://<LOCAL_IP>:9283` - LAN access (no SSL)

### Features

- **Auto HTTPS**: Caddy obtains Let's Encrypt certs automatically
- **Auto Renewal**: Certs renew before expiry
- **HTTP Redirect**: Port 80 automatically redirects to HTTPS

---

## Database Access

### Remote MySQL Access

The MySQL port is exposed on port 59103 for external access:

| Setting | Value |
|---------|-------|
| Host | Remote server IP |
| Port | 59103 |
| Database | savings_db |
| Username | root |
| Password | (from .env DB_ROOT_PASSWORD) |

### Docker Compose Configuration

```yaml
services:
  db:
    image: mysql:8.0
    ports:
      - "59103:3306"
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE: ${DB_NAME}
```

### Running SQL Commands

```bash
# Via Docker context
docker --context remote exec oc-test-php-db-1 mysql -uroot -proot savings_db -e "SQL HERE"

# Via external client
mysql -h <REMOTE_IP> -P 59103 -uroot -p<password> savings_db
```

---

## Application Access

After deployment:

| Service | URL |
|---------|-----|
| App (HTTPS) | `https://your-domain.com` |
| App (LAN) | `http://<LOCAL_IP>:9283` |
| MySQL (Remote) | `<REMOTE_IP>:59103` |

---

## Security Features

### Authentication & Sessions
- CSRF tokens on all forms (validated on POST requests)
- Session regeneration on login (prevents session fixation)
- 30-minute session timeout on inactivity
- Secure cookie settings (httponly, secure, SameSite=Lax)
- Rate limiting: 5 login attempts per 15 minutes lockout

### Input & Output
- SQL injection prevention via PDO prepared statements
- XSS prevention via htmlspecialchars output escaping
- Server-side file upload validation (MIME type + extension whitelist)
- Password complexity requirements (minimum 8 characters)
- Input trimming to prevent whitespace issues

### HTTP Security Headers
Configured in Caddyfile:
```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=()
```

### Upload Protection
- `uploads/.htaccess` disables PHP execution
- Server-side MIME type validation using `finfo`
- Extension whitelist enforcement

### Error Handling
- Database errors logged server-side
- Generic error messages shown to users
- No sensitive information exposed in error output

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
- **HTTPS**: Use Caddy or similar reverse proxy for production deployments.
- **Environment Variables**: Never commit `.env` files to version control.
