#!/bin/bash

echo "Verificando la conexión a PostgreSQL..."

# Verificar si las extensiones de PostgreSQL están instaladas
if ! php -m | grep -q pdo_pgsql; then
    echo "La extensión pdo_pgsql no está instalada. Instalando..."
    sudo apt-get update
    sudo apt-get install -y php-pgsql
    echo "Extensión instalada. Es posible que necesites reiniciar PHP-FPM o el servidor web."
else
    echo "✓ Extensión pdo_pgsql instalada correctamente."
fi

# Intentar conectarse a PostgreSQL usando psql
if command -v psql &> /dev/null; then
    echo "Intentando conectar a PostgreSQL..."
    if PGPASSWORD=Gokussj4 psql -h 127.0.0.1 -U mercer2511 -d laravel -c "SELECT 1" > /dev/null 2>&1; then
        echo "✓ Conexión a PostgreSQL exitosa."
    else
        echo "❌ Error al conectar a PostgreSQL. Verificando el estado del servicio..."
        
        # Verificar si PostgreSQL está en ejecución
        if sudo service postgresql status | grep -q "active (running)"; then
            echo "✓ El servicio PostgreSQL está en ejecución."
        else
            echo "El servicio PostgreSQL no está en ejecución. Intentando iniciarlo..."
            sudo service postgresql start
            sleep 2
            
            if sudo service postgresql status | grep -q "active (running)"; then
                echo "✓ Servicio PostgreSQL iniciado correctamente."
            else
                echo "❌ No se pudo iniciar el servicio PostgreSQL."
            fi
        fi
        
        # Verificar si el usuario existe
        if sudo -u postgres psql -c "SELECT 1 FROM pg_roles WHERE rolname='mercer2511'" | grep -q "1 row"; then
            echo "✓ El usuario mercer2511 existe en PostgreSQL."
        else
            echo "El usuario mercer2511 no existe. Creándolo..."
            sudo -u postgres psql -c "CREATE USER mercer2511 WITH PASSWORD 'Gokussj4' CREATEDB;"
            echo "✓ Usuario creado."
        fi
        
        # Verificar si la base de datos existe
        if sudo -u postgres psql -c "SELECT 1 FROM pg_database WHERE datname='laravel'" | grep -q "1 row"; then
            echo "✓ La base de datos laravel existe."
            
            # Verificar permisos
            echo "Verificando permisos de la base de datos..."
            sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE laravel TO mercer2511;"
            echo "✓ Permisos actualizados."
        else
            echo "La base de datos laravel no existe. Creándola..."
            sudo -u postgres psql -c "CREATE DATABASE laravel OWNER mercer2511;"
            echo "✓ Base de datos creada."
        fi
    fi
else
    echo "❌ El cliente psql no está instalado. Por favor, instálalo con: sudo apt-get install postgresql-client"
fi

echo "Verificando la conexión desde Laravel..."
cd /home/mercer2511/proyectos/Prueba1
php artisan db:show

echo "Proceso de verificación completado."
