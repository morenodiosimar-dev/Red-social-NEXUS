# Proyecto NORE - Laravel Chat Application

## Repository Structure

- **`laravel_chat/`** - Main Laravel application for deployment
- **`archives/`** - Archived legacy applications (not for deployment)
  - `Chat/` - Legacy Node.js chat system
  - `Proyecto_Prueba/` - Legacy PHP application
  - `database_validator.php` - Utility script

## Deployment

This repository is configured for deployment on Railway.

### Railway Configuration
- **Region**: us-east4
- **Build**: Nixpacks (PHP detection)
- **Root Directory**: Repository root (Nixpacks configured to build from `laravel_chat/`)

### Quick Start

1. **Local Development**:
   ```bash
   cd laravel_chat
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan serve
   ```

2. **Railway Deployment**:
   - Connect this Git repository to Railway
   - Add MySQL database plugin
   - Configure environment variables (see `.env.example`)
   - Deploy!

## Documentation

See [laravel_chat/DESPLEGUE_COMPLETO.md](laravel_chat/DESPLEGUE_COMPLETO.md) for detailed deployment instructions.

## Architecture

This is a Laravel 10 chat application that uses:
- **Laravel Echo + Pusher** for real-time messaging
- **Shared authentication** with existing PHP system
- **MySQL** database (nexus_db)

## Archive Information

The `archives/` directory contains legacy applications that were replaced by the Laravel system:
- **Chat/**: Original Node.js chat implementation
- **Proyecto Prueba/**: Development/testing PHP application

These are preserved for reference but are NOT deployed.
