# Instrucciones para Copilot en Proyecto de Carrito de Compras con Laravel

## Descripción General del Proyecto
Este proyecto implementa un carrito de compras con Laravel 12 que permite a los visitantes agregar productos a su carrito y completar compras. El proyecto utiliza Vue.js para el frontend con Inertia.js para la comunicación entre frontend y backend. La autenticación de Laravel ya está implementada a través del starter kit.

## Tecnologías Principales

### Laravel 12
Laravel es un framework de PHP que sigue el patrón MVC (Modelo-Vista-Controlador) donde:
- **Modelos**: Representan las entidades de la base de datos y su lógica de negocio (`app/Models/`)
- **Controladores**: Manejan las solicitudes HTTP y coordinan la respuesta (`app/Http/Controllers/`)
- **Vistas**: En nuestro caso, utilizamos Inertia con Vue para renderizar la interfaz de usuario

### Inertia.js
Inertia.js no es un framework, sino un adaptador que permite crear aplicaciones SPA (Single Page Applications) sin necesidad de construir una API separada:
- Permite usar controladores y rutas de Laravel como lo harías normalmente
- Envía datos a componentes Vue en lugar de vistas tradicionales
- Gestiona la navegación del lado del cliente sin recargar la página completa

### Vue.js con TypeScript
El frontend está construido con Vue.js y TypeScript para proporcionar tipado estático:
- Los componentes utilizan la Composition API con `<script setup lang="ts">`
- Se compila mediante Vite para un desarrollo rápido y optimizado

## Arquitectura y Componentes Principales

### Modelos de Dominio
El proyecto sigue estas entidades de dominio clave:
- **Item**: Productos con `id`, `name`, `quantity`, `price`
- **Customer**: Usuarios con `first_name`, `last_name` y direcciones relacionadas
- **Address**: Ubicaciones de envío con `line_1`, `line_2`, `city`, `state`, `zip`
- **Cart/Order**: Carrito de compras que tiene un cliente, una dirección de envío y múltiples productos

### Flujo de Datos
1. Los componentes Vue interactúan con el backend a través de peticiones Inertia
2. El estado del carrito se gestiona a través de la sesión o base de datos, dependiendo del estado de autenticación
3. Los pedidos son procesados por controladores que manejan la validación, cálculo de impuestos y determinación de tarifas de envío

## Estructura del Proyecto

### Estructura de Directorios Importantes
```
app/
  Models/            # Modelos Eloquent
  Http/
    Controllers/     # Controladores
    Requests/        # Clases de validación de formularios
  Services/          # Servicios (Tax, Shipping, etc.)
resources/
  js/
    Pages/          # Páginas Vue (punto de entrada para Inertia)
    Components/     # Componentes Vue reutilizables
    Composables/    # Lógica de composición compartida de Vue
routes/
  web.php           # Rutas web principales
  api.php           # Rutas API (si se requieren endpoints API directos)
database/
  migrations/       # Migraciones de base de datos
  factories/        # Factories para testing
  seeders/          # Seeders para datos iniciales
```

## Convenciones y Patrones Clave

### Convenciones de Modelos
- Los modelos se ubican en `app/Models/`
- Sigue las convenciones de Eloquent con relaciones adecuadas
- **IMPORTANTE**: El modelo `User.php` ya existe y debe extenderse para admitir campos de Cliente
- Usar `protected $fillable` para definir qué campos se pueden asignar masivamente
- Definir relaciones usando métodos como `hasMany()`, `belongsTo()`, etc.

### Convenciones de Autenticación
- **IMPORTANTE**: Laravel utiliza el modelo `User` para la autenticación por defecto
- La tabla de usuarios debe tener siempre un campo `id` como clave primaria
- Si necesitas personalizar el guardián de autenticación, configúralo en `config/auth.php`
- Para mantener la funcionalidad de "recordarme", mantén el campo `remember_token` en la tabla `users`

### Estructura de Inertia y Vue
- Las páginas de Vue se colocan en `resources/js/Pages/`
- Para renderizar una página desde un controlador: `return Inertia::render('NombrePagina', ['datos' => $datos]);`
- Los formularios deben usar `useForm` de Inertia para mantener el estado y enviar peticiones

## Flujo de Desarrollo

### Configuración del Entorno
```bash
# Iniciar el servidor de desarrollo
php artisan serve

# Compilar assets del frontend (en otra terminal)
npm run dev
```

### Configuración de Base de Datos
El proyecto utiliza PostgreSQL como base de datos principal:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=mercer2511
DB_PASSWORD=Gokussj4
```

**Nota importante**: Al configurar PostgreSQL en WSL2, asegúrate de:
1. Usar `127.0.0.1` como host en lugar de nombres de host personalizados
2. Verificar que el servicio PostgreSQL esté ejecutándose en WSL2
3. Confirmar que el usuario tenga permisos adecuados en la base de datos

### Operaciones de Base de Datos
```bash
# Ejecutar migraciones
php artisan migrate

# Sembrar datos de prueba
php artisan db:seed
```

### Testing
```bash
# Ejecutar tests de PHP
php artisan test

# Ejecutar tests de JavaScript
npm run test
```

## Detalles Críticos de Implementación

### Cálculo de Impuestos
Aplicar tasa de impuesto del 16% al subtotal del carrito utilizando una clase de servicio dedicada:

```php
namespace App\Services;

class TaxService
{
    const TAX_RATE = 0.16;
    
    public function calculateTax($subtotal)
    {
        return $subtotal * self::TAX_RATE;
    }
}
```

### Integración de API de Envío
Las tarifas de envío deben ser manejadas a través de una clase de servicio que simule llamadas a API externas:

```php
namespace App\Services;

class ShippingService
{
    public function calculateRate($address)
    {
        // Simula cálculo de tarifa basado en el código postal
        return 150.00; // Valor fijo para pruebas
    }
}
```

### Procesamiento de Pagos
Para el escenario de prueba, implementar un formulario de pago simple que acepte:
- Tarjeta: 4111 1111 1111 1111
- Titular: TEST (Éxito) o FAIL (Rechazado)
- Fecha: Cualquier fecha posterior a hoy, p.ej. 12/29

## Patrones de Código a Seguir

### Controladores
```php
// Ejemplo de patrón para controladores
public function store(StoreItemRequest $request)
{
    $validated = $request->validated();
    $item = Item::create($validated);
    
    return Inertia::render('Items/Show', [
        'item' => new ItemResource($item)
    ]);
}
```

### Componentes Vue
```vue
<!-- Ejemplo de patrón para componentes Vue -->
<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Definiciones de tipos
interface CartItem {
  id: number;
  name: string;
  price: number;
  quantity: number;
}

// Lógica del componente
const items = ref<CartItem[]>([]);

// Uso de formularios de Inertia
const form = useForm({
  quantity: 1,
});
</script>

<template>
  <AppLayout title="Carrito de Compras">
    <!-- Contenido del componente -->
  </AppLayout>
</template>
```

## Consejos Específicos del Proyecto
1. **Inertia.js**: Utiliza `Link` para navegación y `useForm` para formularios en lugar de elementos HTML estándar
2. **Validación**: Define reglas de validación en clases Form Request dedicadas (`app/Http/Requests/`)
3. **Autenticación**: Extiende el modelo `User` para la funcionalidad del cliente, no crees un modelo separado
4. **Transacciones**: Usa transacciones de base de datos para el procesamiento de pedidos para garantizar la consistencia
5. **Vite**: Los activos frontend se compilan con Vite - los cambios requieren recompilar los activos
6. **Middleware**: Usa middleware para proteger rutas que requieren autenticación (`->middleware(['auth'])`)

## Integración de Inertia con Laravel
Para renderizar componentes Vue desde controladores Laravel:

```php
// En un controlador
public function index()
{
    $items = Item::all();
    
    return Inertia::render('Items/Index', [
        'items' => ItemResource::collection($items)
    ]);
}
```

El componente Vue correspondiente debe existir en `resources/js/Pages/Items/Index.vue`
