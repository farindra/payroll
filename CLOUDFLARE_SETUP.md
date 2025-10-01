# Cloudflare Zero Trust Configuration for Caddy/FrankenPHP

This setup uses Cloudflare Zero Trust (formerly Cloudflare Access) to securely expose your application without public exposure.

## Prerequisites

1. **Cloudflare Account**: With domain `payroll.farindra.com` managed by Cloudflare
2. **Zero Trust Plan**: Free or paid Cloudflare Zero Trust plan
3. **Cloudflare Tunnel**: cloudflared tunnel agent

## Cloudflare Zero Trust Setup

### 1. Create Zero Trust Application

1. Go to Cloudflare Dashboard → Zero Trust → Access → Applications
2. Click "Add an application"
3. Choose "Self-hosted" application type
4. Configure:
   - **Application Name**: Filament Payroll App
   - **Session Duration**: 24h (or as needed)
   - **Path**: `https://payroll.farindra.com`

### 2. Configure Access Policies

Create policies to control who can access the application:

#### Example Policy: Email Domain
```
Action: Allow
Identity: Email ends with @yourcompany.com
```

#### Example Policy: Specific Emails
```
Action: Allow
Identity: Email in [admin@yourcompany.com, user@yourcompany.com]
```

#### Example Policy: 2FA Required
```
Action: Allow
Identity: Email ends with @yourcompany.com
Require: 2FA authentication
```

### 3. Create Cloudflare Tunnel

#### Method 1: Using Cloudflare Dashboard (Recommended)

1. Go to Zero Trust → Networks → Tunnels
2. Click "Create a tunnel"
3. Choose **Cloudflared** connector
4. Install cloudflared on your server:
   ```bash
   # Download cloudflared
   wget https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
   sudo dpkg -i cloudflared-linux-amd64.deb

   # Authenticate
   cloudflared tunnel login

   # Create tunnel
   cloudflared tunnel create payroll-tunnel
   ```

5. Configure tunnel route:
   ```bash
   cloudflared tunnel route dns payroll-tunnel payroll.farindra.com
   ```

6. Create config file `/etc/cloudflared/config.yml`:
   ```yaml
   tunnel: payroll-tunnel
   credentials-file: /etc/cloudflared/.cloudflared/tunnel-credentials.json

   ingress:
     - hostname: payroll.farindra.com
       service: http://localhost:8282
     - service: http_status:404
   ```

7. Start the tunnel:
   ```bash
   sudo cloudflared tunnel run payroll-tunnel
   ```

#### Method 2: Docker Method

Create a `docker-compose.cloudflare.yml`:
```yaml
version: '3.8'

services:
  cloudflared:
    image: cloudflare/cloudflared:latest
    container_name: cloudflared-tunnel
    restart: unless-stopped
    environment:
      - TUNNEL_TOKEN=your_tunnel_token_here
    command: tunnel run
```

### 4. Docker Integration

Update your `docker-compose.yml` to ensure Zero Trust headers are passed:
```yaml
services:
  app:
    # ... existing config
    environment:
      - TRUSTED_PROxies=*
      - CF_VISITOR=trust
```

## Access Points

After Zero Trust configuration:
- **Secure Access**: https://payroll.farindra.com (via Zero Trust)
- **Local Development**: http://localhost:8282 (direct access)
- **Cloudflare Dashboard**: https://one.dash.cloudflare.com

## Security Features

### Zero Trust Provides:
✅ **Identity Verification**: Email, SSO, 2FA authentication
✅ **Device Posture**: Check device health and compliance
✅ **Location Based**: Restrict by country or IP
✅ **Time-based Access**: Schedule access windows
✅ **Audit Logging**: Complete access logs
✅ **Session Control**: Manage session duration

### Application Security:
✅ **No Public Exposure**: Application not directly accessible
✅ **Encrypted Traffic**: End-to-end encryption
✅ **Header Verification**: CF headers for additional security
✅ **Rate Limiting**: Built-in DDoS protection

## Troubleshooting

### Tunnel Connection Issues
```bash
# Check tunnel status
cloudflared tunnel list
cloudflared tunnel info payroll-tunnel

# Check tunnel logs
sudo journalctl -u cloudflared -f

# Test connection
curl -H "Host: payroll.farindra.com" http://localhost:8282
```

### Zero Trust Access Issues
1. Check Access policies in Zero Trust dashboard
2. Verify user authentication status
3. Check session duration settings
4. Review audit logs for denied access

### DNS Configuration
```bash
# Check DNS resolution
nslookup payroll.farindra.com

# Verify CNAME record points to tunnel
dig payroll.farindra.com
```

## Advanced Configuration

### Custom Application Settings
In Zero Trust → Access → Applications → Your App:
- **Custom Pages**: Brand login/error pages
- **Session Settings**: Duration, timeout, concurrent sessions
- **Device Requirements**: OS, browser versions, certificates
- **Geofencing**: Allow/deny specific countries

### Additional Security Headers
Add to Caddyfile:
```caddyfile
header {
    # Cloudflare Zero Trust headers
    CF-Connecting-IP {http.request.remote.host}
    CF-Visitor {http.request.cf.visitor}
    CF-Ray {http.request.cf-ray}
    # Security headers
    X-Content-Type-Options "nosniff"
    X-Frame-Options "DENY"
    Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'"
}
```

### Service Policies
Create service-specific policies:
- **API Access**: Different policies for API endpoints
- **Admin Routes**: Additional authentication for admin areas
- **Report Access**: Time-based access for report generation

## Monitoring and Logging

### Zero Trust Dashboard
- Access logs and audit trails
- User activity monitoring
- Security event alerts
- Performance metrics

### Application Logging
```bash
# View Caddy access logs
docker-compose logs -f app

# View tunnel logs
docker-compose logs -f cloudflared
```

## Backup and Recovery

### Tunnel Configuration
- Export tunnel configuration regularly
- Backup tunnel credentials file
- Document access policies

### Disaster Recovery
- Secondary tunnel configuration
- Backup DNS settings
- Document restoration process

This setup provides enterprise-grade security for your Filament Payroll application using Cloudflare Zero Trust.