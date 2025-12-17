# 🐳 Docker Setup for Laravel 12 + Vite

## Architecture Overview

This Docker setup provides a **clean, production-ready architecture** for Laravel 12 with Vite, following DevOps best practices and the principle of **separation of concerns**.

### Container Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        Host Machine                          │
│  Browser: http://localhost:8080 → Laravel                    │
│  Browser: http://localhost:5173 → Vite HMR                  │
└─────────────────────────────────────────────────────────────┘
                              │
                ┌─────────────┴─────────────┐
                │   Docker Network          │
                │   (devtrack_network)      │
                └───────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┬─────────────┐
        │                     │                     │             │
    ┌───▼────┐         ┌──────▼─────┐       ┌──────▼─────┐   ┌──▼───┐
    │  web   │────────▶│    app     │       │   vite     │   │  db  │
    │ Nginx  │         │ PHP-FPM    │       │  Node.js   │   │MySQL │
    │:8080   │         │ Laravel 12 │       │  Vite Dev  │   │:3306 │
    └────────┘         └────────────┘       └────────────┘   └──────┘
```

### Services

1. **app** (PHP 8.3 FPM)
   - Runs Laravel backend
   - **NO Node.js** - pure PHP environment
   - Communicates with database
   - Processes PHP requests from Nginx

2. **web** (Nginx)
   - Entry point for HTTP traffic
   - Serves static files
   - Proxies PHP requests to `app:9000`
   - Exposed at `localhost:8080`

3. **db** (MySQL 8)
   - Database server
   - Persistent storage via Docker volume
   - Health checks for reliability

4. **vite** (Node.js 20 LTS)
   - **Dedicated frontend build tool**
   - Runs `npm run dev` for HMR
   - Uses **named volume** for `node_modules`
   - File changes trigger instant rebuilds
   - Exposed at `localhost:5173`

---

## 🔑 Key Design Decisions

### 1. **Separation of Concerns**
- PHP container does **NOT** have Node.js
- Frontend and backend are truly decoupled
- Each service has a single responsibility

### 2. **Named Volume for node_modules**
- `node_modules` lives in a Docker volume, **not bind-mounted**
- Avoids cross-platform compatibility issues (Windows/Mac/Linux)
- Faster filesystem performance
- Prevents host `node_modules` conflicts

### 3. **Hot Module Replacement (HMR)**
- Vite server binds to `0.0.0.0` inside container
- Browser connects to `localhost:5173`
- File watching uses polling for Docker compatibility
- Changes in `resources/` trigger instant rebuilds

### 4. **Multi-Stage Dockerfile**
- **Development stage**: includes Xdebug, source mounting
- **Production stage**: optimized, cached routes/config, no dev deps
- Single Dockerfile for both environments

### 5. **Health Checks**
- MySQL has health checks
- `app` service waits for healthy database
- Prevents startup race conditions

---

## 📦 What Gets Created

### Files Created/Modified:
- `docker-compose.yml` - Orchestrates all 4 services
- `Dockerfile` - Multi-stage PHP-FPM image
- `Dockerfile.vite` - Node.js container for Vite
- `docker/nginx/nginx.conf` - Nginx configuration
- `vite.config.js` - Updated for Docker networking
- `.env.docker` - Example environment file

### Docker Volumes:
- `db_data` - MySQL database persistence
- `vendor_data` - PHP dependencies (prevents host override)
- `node_modules` - Node dependencies (isolated from host)

---

## 🚀 Setup Instructions

### 1. Initial Setup

```powershell
# Copy the Docker environment file
Copy-Item .env.docker .env

# Build and start all containers
docker-compose up -d --build

# Wait for containers to be ready (check logs)
docker-compose logs -f
```

### 2. Install Dependencies

```powershell
# Install PHP dependencies inside the container
docker-compose exec app composer install

# Generate Laravel application key
docker-compose exec app php artisan key:generate

# Run database migrations
docker-compose exec app php artisan migrate

# Set proper permissions
docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
```

### 3. Verify Everything Works

```powershell
# Check all containers are running
docker-compose ps

# Check Vite is running
# You should see Vite dev server logs
docker-compose logs vite

# Check app is healthy
docker-compose exec app php artisan --version
```

### 4. Access the Application

- **Laravel App**: http://localhost:8080
- **Vite HMR**: http://localhost:5173 (auto-connected by @vite())
- **MySQL**: `localhost:3306` (accessible from host tools)

---

## 🔥 How Vite HMR Works

### The Flow:

1. **Developer edits** `resources/js/app.js` or `resources/css/app.css`

2. **Docker detects change** via polling (`watch.usePolling: true`)

3. **Vite rebuilds** instantly inside the `vite` container

4. **Browser receives HMR update** from `localhost:5173`

5. **Page updates** without full reload ⚡



### In Laravel Blade:

```blade
<!DOCTYPE html>
<html>
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
```

Laravel automatically detects `public/hot` (created by Vite) and connects to the dev server.

---

## 🛠️ Common Commands

### Container Management
```powershell
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Rebuild after Dockerfile changes
docker-compose up -d --build

# View logs
docker-compose logs -f [service_name]

# Restart a specific service
docker-compose restart vite
```

### Laravel Commands
```powershell
# Run artisan commands
docker-compose exec app php artisan [command]

# Clear caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear

# Run tests
docker-compose exec app php artisan test
```

### Database
```powershell
# Access MySQL CLI
docker-compose exec db mysql -u devtrack -psecret devtrack

# Run migrations
docker-compose exec app php artisan migrate

# Seed database
docker-compose exec app php artisan db:seed
```

### Node/Vite
```powershell
# Install new npm package
docker-compose exec vite npm install [package-name]

# Build for production
docker-compose exec vite npm run build

# Check Vite logs
docker-compose logs -f vite
```

---

## 🐛 Troubleshooting

### Issue: Vite HMR not working

**Symptoms**: File changes don't trigger updates

**Solution**:
```powershell
# 1. Check Vite is running
docker-compose logs vite

# 2. Restart Vite container
docker-compose restart vite

# 3. Clear browser cache
# 4. Verify vite.config.js has usePolling: true
```

### Issue: "Connection refused" to database

**Symptoms**: Laravel can't connect to MySQL

**Solution**:
```powershell
# 1. Check DB is healthy
docker-compose ps

# 2. Wait for health check to pass
docker-compose logs db

# 3. Verify .env has DB_HOST=db (not localhost)
```

### Issue: Nginx shows 502 Bad Gateway

**Symptoms**: Can't access Laravel app

**Solution**:
```powershell
# 1. Check PHP-FPM is running
docker-compose ps app

# 2. Check PHP-FPM logs
docker-compose logs app

# 3. Verify permissions
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
```

### Issue: node_modules out of sync

**Symptoms**: New packages not available

**Solution**:
```powershell
# Rebuild Vite container (reinstalls node_modules)
docker-compose up -d --build vite
```

---

## 🔐 Security Considerations

### Development Environment:
- ✅ `.env.docker` has weak passwords (by design)
- ✅ Xdebug enabled in `app` container
- ✅ Ports exposed to host for debugging

### Production Recommendations:
1. **Use production stage**: `docker-compose -f docker-compose.prod.yml up`
2. **Strong passwords**: Never use `secret` in production
3. **Remove Xdebug**: Use `production` stage in Dockerfile
4. **HTTPS only**: Use Nginx with SSL certificates
5. **Environment variables**: Use Docker secrets or vault
6. **Build assets**: Run `npm run build`, serve from Nginx
7. **Disable debug**: `APP_DEBUG=false`

---

## 📊 Production Evolution

### Current Setup (Development):
- Vite dev server with HMR
- Source code bind-mounted
- Xdebug enabled
- Debug mode on

### Production Changes:

1. **Build Frontend Assets**:
   ```dockerfile
   # Add to Dockerfile (new stage)
   FROM node:20-alpine AS frontend-builder
   WORKDIR /app
   COPY package*.json ./
   RUN npm ci
   COPY . .
   RUN npm run build
   
   # Copy built assets to PHP container
   FROM php:8.3-fpm AS production
   COPY --from=frontend-builder /app/public/build /var/www/html/public/build
   ```

2. **Remove Vite Service**:
   - Production doesn't need live dev server
   - Assets are pre-built and served by Nginx

3. **Optimize Nginx**:
   - Serve `/build` directory statically
   - Enable gzip compression
   - Add caching headers

4. **Environment**:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   ```

5. **Use Docker Secrets**:
   ```yaml
   secrets:
     db_password:
       external: true
   services:
     app:
       secrets:
         - db_password
   ```

---


### Next Steps:

- ✅ Run `docker-compose up -d --build`
- ✅ Access http://localhost:8080
- ✅ Edit `resources/js/app.js` and see instant updates
- ✅ Enjoy clean, fast development! 🚀

---

- [Laravel Vite Documentation](https://laravel.com/docs/12.x/vite)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Vite Configuration](https://vitejs.dev/config/)
- [PHP-FPM Best Practices](https://www.php.net/manual/en/install.fpm.php)

---

