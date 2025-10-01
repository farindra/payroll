# Cloudflare DNS Configuration for Caddy/FrankenPHP

This setup enables automatic SSL certificate issuance using Cloudflare DNS challenge.

## Prerequisites

1. **Cloudflare Account**: You need a Cloudflare account with your domain `payroll.farindra.com` managed by Cloudflare
2. **Cloudflare API Token**: Create an API token with the following permissions:
   - Zone - DNS - Edit
   - Zone - Zone - Read

## Creating Cloudflare API Token

1. Go to your Cloudflare Dashboard → My Profile → API Tokens → Create Token
2. Use the "Edit zone DNS" template
3. Select your specific zone (domain)
4. Continue to summary and create the token
5. **Copy the token immediately** - you won't be able to see it again

## Configuration Steps

### 1. Update Caddyfile

Edit `/app/caddy/Caddyfile` and replace:
- `your-email@example.com` → your real email address
- `YOUR_CLOUDFLARE_API_TOKEN` → your actual Cloudflare API token

```caddyfile
{
    email your-real-email@example.com
    acme_dns cloudflare {
        api_token your_actual_api_token_here
    }
}
```

### 2. DNS Configuration in Cloudflare

Make sure your DNS records in Cloudflare are set up correctly:

```
Type: A
Name: payroll
Content: YOUR_SERVER_IP
Proxy status: DNS only (orange cloud off)
TTL: Auto
```

**Important**: Turn off the proxy (orange cloud) for the DNS record to use the DNS challenge.

### 3. Environment Variables (Optional)

For better security, you can use environment variables instead of hardcoding the API token:

In your `.env` file:
```
CLOUDFLARE_EMAIL=your-email@example.com
CLOUDFLARE_API_TOKEN=your_actual_api_token_here
```

Then update the Caddyfile:
```caddyfile
{
    email {$CLOUDFLARE_EMAIL}
    acme_dns cloudflare {
        api_token {$CLOUDFLARE_API_TOKEN}
    }
}
```

### 4. Update docker-compose.yml

Add the environment variables to your docker-compose.yml:

```yaml
environment:
    - CLOUDFLARE_EMAIL=${CLOUDFLARE_EMAIL}
    - CLOUDFLARE_API_TOKEN=${CLOUDFLARE_API_TOKEN}
    # ... other variables
```

## How It Works

1. **DNS Challenge**: Caddy uses Cloudflare's DNS API to prove domain ownership
2. **Automatic HTTPS**: Caddy automatically obtains and renews SSL certificates
3. **Port Configuration**:
   - HTTP: Port 80 (redirects to HTTPS)
   - HTTPS: Port 443 (secure connection)

## Access Points

After configuration:
- **HTTP**: http://payroll.farindra.com (redirects to HTTPS)
- **HTTPS**: https://payroll.farindra.com (secure)
- **Local**: http://localhost:8282 (no redirect)

## Troubleshooting

### Certificate Issues
```bash
# Check Caddy logs
docker-compose logs -f app

# Check certificate status
curl -I https://payroll.farindra.com
```

### DNS Issues
```bash
# Check DNS resolution
nslookup payroll.farindra.com

# Make sure proxy is disabled (orange cloud off in Cloudflare)
```

### API Token Issues
- Verify the token has correct permissions
- Ensure the token is not expired
- Check for typos in the token

## Security Notes

- Keep your API token secure
- Use environment variables instead of hardcoding
- Regularly rotate your API tokens
- Use the minimum required permissions

## Alternative: Manual SSL

If Cloudflare DNS doesn't work, you can use Cloudflare's own SSL:
1. Enable "Full (strict)" SSL mode in Cloudflare
2. Use Cloudflare's origin certificate
3. Configure Caddy to use the certificate