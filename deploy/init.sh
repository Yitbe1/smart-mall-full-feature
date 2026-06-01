#!/bin/bash
set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

PHP_BIN="${PHP_BIN:-$(command -v php 2>/dev/null || echo '/opt/lampp/bin/php')}"

echo "============================================"
echo "  Smart Mall First-Time Setup"
echo "============================================"
echo ""

if [ ! -f "$PROJECT_DIR/.env" ] && [ -f "$PROJECT_DIR/.env.example" ]; then
    cp "$PROJECT_DIR/.env.example" "$PROJECT_DIR/.env"
    echo -e "${GREEN}[OK]${NC} Created .env from .env.example"
    echo -e "${YELLOW}[ACTION REQUIRED]${NC} Edit $PROJECT_DIR/.env with your database credentials and settings."
    echo ""
elif [ ! -f "$PROJECT_DIR/.env" ]; then
    cat > "$PROJECT_DIR/.env" << 'EOF'
DB_HOST=localhost
DB_NAME=smartmall_db
DB_USER=root
DB_PASS=
CHAPA_SECRET_KEY=your_chapa_secret_key_here
APP_ENV=development
EOF
    echo -e "${GREEN}[OK]${NC} Created default .env"
    echo -e "${YELLOW}[ACTION REQUIRED]${NC} Edit $PROJECT_DIR/.env with your settings."
    echo ""
else
    echo -e "${GREEN}[OK]${NC} .env already exists"
fi

mkdir -p "$PROJECT_DIR/logs" "$PROJECT_DIR/backups"
echo -e "${GREEN}[OK]${NC} Created logs/ and backups/ directories"

find "$PROJECT_DIR/logs" -type d -exec chmod 755 {} \;
find "$PROJECT_DIR/backups" -type d -exec chmod 755 {} \;
echo -e "${GREEN}[OK]${NC} Set directory permissions (755)"

touch "$PROJECT_DIR/logs/.gitkeep" "$PROJECT_DIR/backups/.gitkeep"
echo -e "${GREEN}[OK]${NC} Created .gitkeep markers"

echo ""
echo "--- PHP Version ---"
$PHP_BIN -v 2>/dev/null || echo -e "${RED}PHP not found at $PHP_BIN${NC}"
echo -e "${GREEN}[OK]${NC} PHP available"

echo ""
read -r -p "Run database migrations now? [y/N] " response
if [[ "$response" =~ ^[Yy]$ ]]; then
    echo ""
    $PHP_BIN "$SCRIPT_DIR/migrate.php"
fi

echo ""
echo -e "${GREEN}Setup complete.${NC}"
echo "============================================"
