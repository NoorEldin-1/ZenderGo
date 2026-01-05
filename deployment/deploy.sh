#!/bin/bash
# ============================================
# ZENDER DEPLOYMENT SCRIPT
# ============================================
# This script automates the deployment process for Zender.
# Run this after uploading your code to the server.
#
# Usage: bash deploy.sh
# ============================================

set -e  # Exit on any error

echo "🚀 Starting Zender Deployment..."
echo "================================"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: artisan file not found. Are you in the Laravel root directory?${NC}"
    exit 1
fi

# Step 1: Put application in maintenance mode
echo -e "\n${YELLOW}Step 1: Enabling maintenance mode...${NC}"
php artisan down --render="errors::503" || true

# Step 2: Pull latest code (if using git)
if [ -d ".git" ]; then
    echo -e "\n${YELLOW}Step 2: Pulling latest code...${NC}"
    git pull origin main || git pull origin master
else
    echo -e "\n${YELLOW}Step 2: Skipping git pull (not a git repository)${NC}"
fi

# Step 3: Install/update composer dependencies
echo -e "\n${YELLOW}Step 3: Installing composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

# Step 4: Run database migrations
echo -e "\n${YELLOW}Step 4: Running database migrations...${NC}"
php artisan migrate --force

# Step 5: Clear and rebuild caches
echo -e "\n${YELLOW}Step 5: Optimizing application...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Step 6: Clear old caches
echo -e "\n${YELLOW}Step 6: Clearing old caches...${NC}"
php artisan cache:clear

# Step 7: Restart queue workers
echo -e "\n${YELLOW}Step 7: Restarting queue workers...${NC}"
php artisan queue:restart

# Step 8: Restart supervisor (if available)
if command -v supervisorctl &> /dev/null; then
    echo -e "\n${YELLOW}Step 8: Restarting supervisor...${NC}"
    sudo supervisorctl reread
    sudo supervisorctl update
    sudo supervisorctl restart zender-worker:*
else
    echo -e "\n${YELLOW}Step 8: Supervisor not found, skipping...${NC}"
fi

# Step 9: Set proper permissions
echo -e "\n${YELLOW}Step 9: Setting permissions...${NC}"
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Step 10: Take application out of maintenance mode
echo -e "\n${YELLOW}Step 10: Disabling maintenance mode...${NC}"
php artisan up

# Step 11: Run health check
echo -e "\n${YELLOW}Step 11: Running health check...${NC}"
curl -s http://localhost/health || echo "Health check endpoint not accessible"

echo ""
echo -e "${GREEN}================================${NC}"
echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo -e "${GREEN}================================${NC}"
echo ""
echo "Next steps:"
echo "1. Verify the application at your domain"
echo "2. Check logs: tail -f storage/logs/laravel.log"
echo "3. Monitor queue: php artisan queue:monitor default"
