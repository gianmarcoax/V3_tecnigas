#!/bin/bash
# ============================================================
# deploy.sh — Script de actualización del Dashboard Tecnigas
#
# USO EN EL VPS:
#   chmod +x deploy.sh   (solo la primera vez)
#   ./deploy.sh
#
# QUÉ HACE:
#   1. git pull → trae el código nuevo
#   2. Rebuild imagen PHP-FPM
#   3. Reiniciar solo los contenedores necesarios
#   4. Correr migraciones si hay nuevas
#   5. Limpiar caché de Laravel
#   6. Nunca elimina datos (volúmenes intactos)
# ============================================================

set -e  # Detener ante cualquier error

# ── Colores para output legible ───────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # Sin color

log_info()    { echo -e "${BLUE}[INFO]${NC}  $1"; }
log_success() { echo -e "${GREEN}[OK]${NC}    $1"; }
log_warning() { echo -e "${YELLOW}[WARN]${NC}  $1"; }
log_error()   { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

# ── Directorio del proyecto ───────────────────────────────────
# Ajustar si el proyecto está en otra ruta del VPS
PROJECT_DIR="/var/www/tecnigas"

echo ""
echo "════════════════════════════════════════════════════"
echo "  🚀 Deploy — Dashboard Tecnigas V3 Laravel"
echo "════════════════════════════════════════════════════"
echo ""

# ── Verificar que estamos en el directorio correcto ──────────
if [ ! -f "$PROJECT_DIR/docker-compose.yml" ]; then
    log_error "No se encontró docker-compose.yml en $PROJECT_DIR"
fi

cd "$PROJECT_DIR"
log_info "Directorio: $(pwd)"

# ── Verificar que .env existe ────────────────────────────────
if [ ! -f ".env" ]; then
    log_error ".env no encontrado. Copiar .env.production como .env y configurarlo."
fi

# ── 1. Git pull ───────────────────────────────────────────────
log_info "Actualizando código desde Git..."
git fetch origin
git pull origin main 2>/dev/null || git pull origin master 2>/dev/null
log_success "Código actualizado."

# ── 2. Build de la imagen PHP-FPM (con cache de Docker) ──────
log_info "Construyendo imagen tecnigas_app..."
docker compose build --no-cache tecnigas_app
log_success "Imagen construida."

# ── 3. Reiniciar solo la app (DB sigue corriendo) ────────────
log_info "Reiniciando contenedores..."
docker compose up -d --no-deps tecnigas_app tecnigas_nginx
log_success "Contenedores actualizados."

# ── Esperar que PHP-FPM arranque ─────────────────────────────
log_info "Esperando que tecnigas_app esté listo (10s)..."
sleep 10

# ── 4. Migraciones de base de datos ──────────────────────────
log_info "Ejecutando migraciones..."
docker compose exec tecnigas_app php artisan migrate --force
log_success "Migraciones aplicadas."

# ── 5. Optimizaciones Laravel (producción) ───────────────────
log_info "Optimizando configuración Laravel..."
docker compose exec tecnigas_app php artisan config:cache
docker compose exec tecnigas_app php artisan route:cache
docker compose exec tecnigas_app php artisan view:cache
docker compose exec tecnigas_app php artisan event:cache
log_success "Caché de Laravel generada."

# ── 6. Limpiar caché de Odoo (opcional — datos frescos) ──────
log_info "Limpiando caché de datos de Odoo..."
docker compose exec tecnigas_app php artisan cache:clear
log_success "Caché de Odoo limpiada."

# ── 7. Status final ───────────────────────────────────────────
echo ""
echo "════════════════════════════════════════════════════"
log_success "Deploy completado exitosamente."
echo ""
docker compose ps
echo "════════════════════════════════════════════════════"
echo ""
