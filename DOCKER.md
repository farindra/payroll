# Docker Setup for Filament Payroll with FrankenPHP

This application can be run using Docker with FrankenPHP, which provides a modern, high-performance PHP server.

## Prerequisites

- Docker and Docker Compose installed
- Supabase database credentials
- Application key (APP_KEY)

## Environment Setup

1. Copy the environment file:
```bash
cp .env.example .env
```

2. Edit `.env` file with your database credentials:
```env
DB_CONNECTION=pgsql-supabase
DB_HOST=your-supabase-host
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.your-username
DB_PASSWORD=your-password
DB_URL=postgresql://postgres.your-username:your-password@your-supabase-host:6543/postgres
```

3. Generate application key:
```bash
php artisan key:generate
```

## Production Setup

1. Build and run the application:
```bash
docker-compose up -d --build
```

2. Check the status:
```bash
docker-compose ps
```

3. View logs:
```bash
docker-compose logs -f app
```

4. Access the application:
- Web: http://localhost
- Caddy Admin: http://localhost:2019

## Development Setup

For development with hot-reload:

1. Build and run with development configuration:
```bash
docker-compose -f docker-compose.dev.yml up -d --build
```

2. Access the application:
- Web: http://localhost:8000
- Caddy Admin: http://localhost:2019

## Useful Commands

### Application Commands

Run Artisan commands inside the container:
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan optimize:clear
```

Access the container shell:
```bash
docker-compose exec app bash
```

### Maintenance

Stop the application:
```bash
docker-compose down
```

Stop and remove volumes:
```bash
docker-compose down -v
```

Rebuild the image:
```bash
docker-compose build --no-cache
```

## Configuration

### FrankenPHP Configuration

The Caddyfile is located at `caddy/Caddyfile` and can be customized for:
- Domain routing
- SSL certificates
- Security headers
- Caching rules

### Environment Variables

Key environment variables that can be set in `.env`:

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_ENV` | Application environment | `production` |
| `APP_DEBUG` | Debug mode | `false` |
| `APP_URL` | Application URL | `http://localhost` |
| `DB_CONNECTION` | Database driver | `pgsql-supabase` |
| `CACHE_DRIVER` | Cache driver | `file` |
| `SESSION_DRIVER` | Session driver | `file` |
| `QUEUE_CONNECTION` | Queue connection | `sync` |

## Volume Mounts

The following directories are mounted as volumes:

- `./storage:/app/storage` - Application storage (uploads, logs, cache)
- `./bootstrap/cache:/app/bootstrap/cache` - Laravel bootstrap cache
- `./caddy:/app/caddy` - Caddy configuration
- `caddy_data:/data` - Caddy persistent data
- `caddy_config:/config` - Caddy configuration

## Health Checks

The application includes health checks that verify:
- The application is responding to HTTP requests
- Database connectivity (if configured)
- Required services are running

## Troubleshooting

### Common Issues

1. **Permission Issues**: Ensure storage directories have proper permissions:
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

2. **Database Connection**: Verify database credentials in `.env` file
3. **Port Conflicts**: Ensure ports 80, 443, and 2019 are available
4. **Application Key**: Generate a new APP_KEY if needed

### Logs

View application logs:
```bash
docker-compose logs -f app
```

View Caddy access logs:
```bash
docker-compose exec app tail -f /var/log/caddy/access.log
```

### Performance

For optimal performance:
- Use Redis for caching in production
- Configure proper SSL certificates
- Enable HTTP/2 and compression
- Set up proper logging levels

## Security

- Change default APP_KEY
- Use environment variables for sensitive data
- Enable SSL in production
- Configure proper CORS headers
- Use security headers in Caddy configuration

## Support

For issues or questions, check the following:
- [FrankenPHP Documentation](https://frankenphp.dev/)
- [Laravel Documentation](https://laravel.com/docs/)
- [Caddy Documentation](https://caddyserver.com/docs/)