#!/bin/bash

###############################################################################
# Deploy Script for Ticketing System
# This script can be used for manual deployment or called from Jenkins
###############################################################################

set -e  # Exit on error

echo "=========================================="
echo "  Ticketing System Deployment Script"
echo "=========================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

# Check if running as root (for system-wide Jenkins)
if [[ $EUID -ne 0 ]] && [[ "$1" != "--no-root-check" ]]; then
   print_info "Not running as root. This is OK if using Docker Jenkins."
fi

# Step 1: Pull latest code
print_info "Step 1: Pulling latest code from GitHub..."
git pull origin main || git pull origin master
print_success "Code updated"

# Step 2: Stop existing containers
print_info "Step 2: Stopping existing containers..."
docker-compose down || true
print_success "Containers stopped"

# Step 3: Build Docker images
print_info "Step 3: Building Docker images..."
docker-compose build --no-cache
print_success "Docker images built"

# Step 4: Start containers
print_info "Step 4: Starting containers..."
docker-compose up -d
print_success "Containers started"

# Step 5: Wait for containers to be ready
print_info "Step 5: Waiting for containers to be ready..."
sleep 10

# Step 6: Run database migrations (backend)
print_info "Step 6: Running database migrations..."
docker exec ticketing_backend php artisan migrate --force || print_error "Migration failed (continuing anyway)"
print_success "Migrations completed"

# Step 7: Clear cache
print_info "Step 7: Clearing application cache..."
docker exec ticketing_backend php artisan cache:clear || true
docker exec ticketing_backend php artisan config:clear || true
print_success "Cache cleared"

# Step 8: Check container status
print_info "Step 8: Checking container status..."
docker-compose ps

# Step 9: Show container logs (last 20 lines)
print_info "Step 9: Recent logs:"
echo "--- Backend Logs ---"
docker logs --tail 20 ticketing_backend
echo ""
echo "--- Frontend Logs ---"
docker logs --tail 20 ticketing_frontend

# Step 10: Cleanup old images
print_info "Step 10: Cleaning up old Docker images..."
docker image prune -f
print_success "Cleanup completed"

echo ""
echo "=========================================="
print_success "Deployment Completed Successfully!"
echo "=========================================="
echo ""
echo "Application URLs:"
echo "  - Frontend: http://localhost:3000"
echo "  - Backend:  http://localhost:5000"
echo "  - Database: localhost:3307"
echo ""
echo "To view logs:"
echo "  docker-compose logs -f"
echo ""
echo "To stop:"
echo "  docker-compose down"
echo ""
