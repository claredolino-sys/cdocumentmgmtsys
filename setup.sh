#!/bin/bash

# CDMIS Quick Setup Script
# This script helps set up the CDMIS application for development

set -e

echo "=================================="
echo "CDMIS Quick Setup Script"
echo "=================================="
echo ""

# Check if we're in the right directory
if [ ! -f "cdmis_db.sql" ]; then
    echo "❌ Error: cdmis_db.sql not found. Please run this script from the project root."
    exit 1
fi

# Check prerequisites
echo "📋 Checking prerequisites..."

# Check PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.0 or higher."
    exit 1
fi
echo "✅ PHP found: $(php --version | head -n 1)"

# Check MySQL
if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL is not installed. Please install MySQL/MariaDB."
    exit 1
fi
echo "✅ MySQL found"

# Check Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed. Please install Node.js 18 or higher."
    exit 1
fi
echo "✅ Node.js found: $(node --version)"

# Check npm
if ! command -v npm &> /dev/null; then
    echo "❌ npm is not installed."
    exit 1
fi
echo "✅ npm found: $(npm --version)"

echo ""
echo "=================================="
echo "Database Setup"
echo "=================================="
echo ""

read -p "Enter MySQL root password: " -s MYSQL_ROOT_PASSWORD
echo ""

# Create database
echo "📦 Creating database..."
mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS cdmis_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;" 2>/dev/null || {
    echo "❌ Failed to create database. Please check your MySQL credentials."
    exit 1
}

# Import schema
echo "📥 Importing database schema..."
mysql -u root -p"$MYSQL_ROOT_PASSWORD" cdmis_db < cdmis_db.sql 2>/dev/null || {
    echo "❌ Failed to import database schema."
    exit 1
}

echo "✅ Database setup complete"

# Create database user (optional)
read -p "Create a dedicated database user? (y/n): " CREATE_USER
if [ "$CREATE_USER" = "y" ]; then
    read -p "Enter username (default: cdmis_user): " DB_USER
    DB_USER=${DB_USER:-cdmis_user}
    
    read -p "Enter password (default: cdmis_password): " DB_PASSWORD
    DB_PASSWORD=${DB_PASSWORD:-cdmis_password}
    
    mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';" 2>/dev/null
    mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "GRANT ALL PRIVILEGES ON cdmis_db.* TO '${DB_USER}'@'localhost';" 2>/dev/null
    mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "FLUSH PRIVILEGES;" 2>/dev/null
    echo "✅ Database user created"
else
    DB_USER="root"
    DB_PASSWORD="$MYSQL_ROOT_PASSWORD"
fi

echo ""
echo "=================================="
echo "Backend Setup"
echo "=================================="
echo ""

cd backend

# Create .env file
if [ ! -f ".env" ]; then
    echo "📝 Creating backend .env file..."
    cp .env.example .env
    
    # Update database credentials
    # Detect OS and set SED_INPLACE variable
    if [[ "$OSTYPE" == "darwin"* ]]; then
        SED_INPLACE="sed -i.bak"
    else
        SED_INPLACE="sed -i"
    fi

    $SED_INPLACE "s/DB_USER=.*/DB_USER=${DB_USER}/" .env
    $SED_INPLACE "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/" .env
    # Remove backup file if it exists (macOS/BSD)
    [ -f .env.bak ] && rm .env.bak
    
    # Generate JWT secret
    JWT_SECRET=$(openssl rand -base64 32)
    $SED_INPLACE "s|JWT_SECRET=.*|JWT_SECRET=${JWT_SECRET}|" .env
    [ -f .env.bak ] && rm .env.bak
    
    echo "✅ Backend .env created"
else
    echo "⚠️  Backend .env already exists, skipping..."
fi

# Set permissions
echo "🔐 Setting file permissions..."
chmod -R 755 public/uploads
echo "✅ Permissions set"

cd ..

echo ""
echo "=================================="
echo "Frontend Setup"
echo "=================================="
echo ""

cd "CDMIS LOG IN PAGE"

# Create .env file
if [ ! -f ".env" ]; then
    echo "📝 Creating frontend .env file..."
    cp .env.example .env
    echo "✅ Frontend .env created"
else
    echo "⚠️  Frontend .env already exists, skipping..."
fi

# Install dependencies
echo "📦 Installing frontend dependencies..."
npm install

echo "✅ Frontend setup complete"

cd ..

echo ""
echo "=================================="
echo "✅ Setup Complete!"
echo "=================================="
echo ""
echo "Next steps:"
echo ""
echo "1. Start the backend server:"
echo "   cd backend && php -S localhost:8000"
echo ""
echo "2. In a new terminal, start the frontend:"
echo "   cd 'CDMIS LOG IN PAGE' && npm run dev"
echo ""
echo "3. Access the application at:"
echo "   http://localhost:3000"
echo ""
echo "4. Create an admin user by running this SQL:"
echo "   INSERT INTO users (school_id, password_hash, full_name, email, role)"
echo "   VALUES ('00-0-00001', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',"
echo "   'System Admin', 'admin@bipsu.edu.ph', 'Admin');"
echo ""
echo "   Login with School ID: 00-0-00001, Password: 1234"
echo "   (Change password after first login)"
echo ""
echo "=================================="
