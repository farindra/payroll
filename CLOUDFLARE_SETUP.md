# Cloudflare Configuration for Caddy/FrankenPHP

This setup uses Cloudflare's proxy mode to provide SSL termination, avoiding the need for DNS challenge modules that may not be available in FrankenPHP.

## Prerequisites

1. **Cloudflare Account**: You need a Cloudflare account with your domain `payroll.farindra.com` managed by Cloudflare
2. **Server IP**: Your server's public IP address

## Configuration Steps

### 1. DNS Configuration in Cloudflare

Set up your DNS records in Cloudflare:

```
Type: A
Name: payroll
Content: YOUR_SERVER_IP
Proxy status: Proxied (orange cloud ON)
TTL: Auto
```

**Important**: Enable the proxy (orange cloud ON) to use Cloudflare's SSL.

### 2. SSL/TLS Settings in Cloudflare

1. Go to SSL/TLS → Overview
2. Choose "Full (strict)" mode
3. This provides end-to-end encryption:
   - Browser ↔ Cloudflare: Encrypted
   - Cloudflare ↔ Your Server: Encrypted

### 3. Edge Certificates

Cloudflare automatically provides:
- Universal SSL coverage
- Wildcard certificates (*.yourdomain.com)
- Automatic certificate renewal
- HSTS preloading

## How It Works

1. **Cloudflare Proxy**: Traffic goes through Cloudflare's network
2. **SSL Termination**: Cloudflare handles SSL certificate management
3. **Origin Connection**: Cloudflare connects to your server over HTTPS or HTTP
4. **Performance**: Benefits from Cloudflare's CDN and caching

## Port Configuration

Since Cloudflare handles the public-facing SSL, you only need:

- **External**: Port 80 and 443 (handled by Cloudflare)
- **Internal**: Your server can run on any port (8282 in this case)

## Access Points

After configuration:
- **HTTPS**: https://payroll.farindra.com (via Cloudflare)
- **HTTP**: http://payroll.farindra.com (redirects to HTTPS via Cloudflare)
- **Local**: http://localhost:8282 (direct access)

## Caddy Configuration

The current Caddyfile is simplified:
- No DNS challenge needed
- No ACME configuration required
- Cloudflare handles all SSL termination

## Benefits

✅ **No Certificate Management**: Cloudflare handles everything
✅ **Better Performance**: CDN and caching benefits
✅ **DDoS Protection**: Cloudflare's security features
✅ **Automatic Renewal**: No expired certificates
✅ **Wildcard Support**: Covers all subdomains

## Troubleshooting

### Connection Issues
```bash
# Check if your server is accessible
curl http://localhost:8282

# Check Cloudflare status
curl -I https://payroll.farindra.com
```

### SSL Issues in Cloudflare
1. Check SSL/TLS mode is "Full (strict)"
2. Verify origin certificate is valid
3. Check mixed content issues

### DNS Propagation
```bash
# Check DNS resolution
nslookup payroll.farindra.com

# Check propagation status
dig payroll.farindra.com
```

## Security Notes

- Cloudflare provides WAF protection
- Always use "Full (strict)" SSL mode
- Keep your server software updated
- Use Cloudflare's firewall rules for additional security

## Advanced Options

### Custom Headers
You can add additional security headers in Cloudflare:
- Content Security Policy
- X-Frame-Options
- X-Content-Type-Options

### Firewall Rules
Set up firewall rules in Cloudflare:
- Block suspicious traffic
- Rate limiting
- Country blocking

### Caching
Configure caching rules:
- Static assets
- API endpoints
- Dynamic content