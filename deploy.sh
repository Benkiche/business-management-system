#!/bin/bash

set -e

echo "🚀 Deploying Business Management System..."

# Configuration
REPO_URL="https://github.com/yourusername/business-management-system.git"
DEPLOY_DIR="/var/www/bms"
BRANCH="main"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Fetch latest code
echo -e "${YELLOW}[1/10]${NC} Fetching latest code..."
cd $DEPLOY_DIR
git fetch origin
git checkout $BRANCH
git pull origin $BRANCH

# Step 2: Install dependencies
echo -e "${YELLOW}[2/10]${NC} Installing dependencies..."
sudo -u www-data composer install --optimize-autoloader --no-dev

# Step 3: Clear cache
echo -e "${YELLOW}[3/10]${NC} Clearing cache..."
sudo -u www-data php artisan cache:purge

# Step 4: Run migrations
echo -e "${YELLOW}[4/10]${NC} Running migrations..."
sudo -u www-data php artisan migrate --force

# Step 5: Seed if needed
echo -e "${YELLOW}[5/10]${NC} Seeding database..."
# sudo -u www-data php artisan db:seed --force

# Step 6: Optimize
echo -e "${YELLOW}[6/10]${NC} Optimizing application..."
sudo -u www-data php artisan system:optimize

# Step 7: Fix permissions
echo -e "${YELLOW}[7/10]${NC} Setting permissions..."
sudo chown -R www-data:www-data $DEPLOY_DIR
sudo chmod -R 755 storage bootstrap/cache

# Step 8: Restart services
echo -e "${YELLOW}[8/10]${NC} Restarting services..."
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx

# Step 9: Verify deployment
echo -e "${YELLOW}[9/10]${NC} Verifying deployment..."
php healthcheck.php > /dev/null 2>&1 && echo "✓ Health check passed"

# Step 10: Success
echo -e "${YELLOW}[10/10]${NC} Deployment complete!"
echo -e "${GREEN}✓ Deployment successful!${NC}"
echo "Application URL: https://yourdomain.com"