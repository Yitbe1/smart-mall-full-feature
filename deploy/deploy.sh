#!/bin/bash
set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

DRY_RUN=false
MIGRATE_ONLY=false
QUICK=false
CHANGED_FILES=()

while [[ $# -gt 0 ]]; do
    case "$1" in
        --dry-run) DRY_RUN=true; shift ;;
        --migrate) MIGRATE_ONLY=true; shift ;;
        --quick) QUICK=true; shift ;;
        --) shift; CHANGED_FILES+=("$@"); break ;;
        *) CHANGED_FILES+=("$1"); shift ;;
    esac
done

if [ -f "$PROJECT_DIR/.env" ]; then
    set -a
    source "$PROJECT_DIR/.env"
    set +a
fi

DB_HOST="${DB_HOST:-localhost}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
DB_NAME="${DB_NAME:-smartmall_db}"

PHP_BIN="${PHP_BIN:-$(command -v php 2>/dev/null || echo '/opt/lampp/bin/php')}"
MYSQL_BIN="${MYSQL_BIN:-$(command -v mysql 2>/dev/null || echo '/opt/lampp/bin/mysql')}"

fail() {
    echo -e "${RED}[FAIL] $1${NC}" >&2
    exit 1
}

ok() {
    echo -e "${GREEN}[OK] $1${NC}"
}

warn() {
    echo -e "${YELLOW}[WARN] $1${NC}"
}

info() {
    echo -e "[INFO] $1"
}

dry() {
    if [ "$DRY_RUN" = true ]; then
        warn "[DRY-RUN] Would run: $1"
    else
        eval "$1"
    fi
}

echo "============================================"
echo "  Smart Mall Deployment Script"
echo "============================================"
echo ""

info "Project: $PROJECT_DIR"
info "PHP:     $PHP_BIN"
info "MySQL:   $MYSQL_BIN"
info "DB:      $DB_HOST/$DB_NAME"
info "Mode:    $([ "$DRY_RUN" = true ] && echo 'DRY-RUN' || echo 'LIVE')"
echo ""

echo "--- Step 0: Checking prerequisites ---"

if ! command -v "$PHP_BIN" &>/dev/null; then
    fail "PHP CLI not found at $PHP_BIN"
fi
ok "PHP CLI available: $($PHP_BIN -r 'echo PHP_VERSION;')"

if ! command -v "$MYSQL_BIN" &>/dev/null; then
    fail "MySQL CLI not found at $MYSQL_BIN"
fi
ok "MySQL CLI available"

echo ""
echo "--- Step 1: Checking config.php ---"

if [ ! -f "$PROJECT_DIR/config.php" ]; then
    fail "config.php not found"
fi

if ! $PHP_BIN -l "$PROJECT_DIR/config.php" >/dev/null 2>&1; then
    fail "config.php has syntax errors"
fi
ok "config.php is valid PHP"

echo ""
echo "--- Step 2: PHP Lint ---"

if [ ${#CHANGED_FILES[@]} -gt 0 ]; then
    for file in "${CHANGED_FILES[@]}"; do
        if [[ "$file" == *.php ]] && [ -f "$file" ]; then
            if $PHP_BIN -l "$file" >/dev/null 2>&1; then
                ok "Lint passed: $file"
            else
                fail "Lint failed: $file"
            fi
        fi
    done
else
    lint_failures=0
    while IFS= read -r -d '' phpfile; do
        if ! $PHP_BIN -l "$phpfile" >/dev/null 2>&1; then
            warn "Lint failed: $phpfile"
            lint_failures=$((lint_failures + 1))
        fi
    done < <(find "$PROJECT_DIR" -name '*.php' -not -path '*/vendor/*' -not -path '*/node_modules/*' -not -path '*/cache/*' -print0)

    if [ "$lint_failures" -eq 0 ]; then
        ok "All PHP files passed lint"
    else
        warn "$lint_failures PHP file(s) failed lint (deployment continuing)"
    fi
fi

echo ""
echo "--- Step 3: Running Tests ---"

if [ "$MIGRATE_ONLY" = true ] || [ "$QUICK" = true ]; then
    info "Skipping tests ($([ "$MIGRATE_ONLY" = true ] && echo '--migrate' || echo '--quick') mode)"
else
    if $PHP_BIN "$PROJECT_DIR/_dev/tests/run.php"; then
        ok "All tests passed"
    else
        warn "Some tests failed — continuing deployment"
    fi
fi

echo ""
echo "--- Step 4: Database Migrations ---"

dry "$PHP_BIN $SCRIPT_DIR/migrate.php"

echo ""
echo "--- Step 5: Deployment Complete ---"

VERSION=$($PHP_BIN -r '
    $f = "'"$PROJECT_DIR"'/.env";
    if (file_exists($f)) {
        $e = parse_ini_file($f);
        echo $e["APP_VERSION"] ?? "1.0.0";
    } else { echo "1.0.0"; }
')

ok "Smart Mall v$VERSION deployed successfully"
echo "============================================"
