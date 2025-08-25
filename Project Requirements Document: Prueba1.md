
# **Documento de Requisitos del Proyecto: Carrito de Compras**

## 1. Requisitos Funcionales

La siguiente tabla detalla los requisitos funcionales de la aplicación de Carrito de Compras.

| ID de Requisito | Descripción                    | Historia de Usuario                                                                                   | Comportamiento/Resultado Esperado                                                                                            |
|----------------|-------------------------------|-------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------|
| FR001          | Mostrar productos             | Como visitante, quiero ver los productos disponibles para poder agregarlos a mi carrito.              | El sistema debe mostrar una lista de productos con nombre, precio y una opción para agregar al carrito.                        |
| FR002          | Agregar producto al carrito   | Como visitante, quiero agregar productos a mi carrito para posteriormente comprarlos.                  | El sistema debe permitir agregar productos al carrito, especificando la cantidad deseada y actualizando el subtotal.          |
| FR003          | Eliminar producto del carrito | Como visitante, quiero poder eliminar productos de mi carrito si cambio de opinión.                    | El sistema debe proporcionar una forma de eliminar productos individuales del carrito.                                        |
| FR004          | Ver contenido del carrito     | Como visitante, quiero ver el contenido de mi carrito para revisar los productos que voy a comprar.    | El sistema debe mostrar todos los productos agregados al carrito con sus cantidades, precios y el subtotal.                   |
| FR005          | Ingresar información personal | Como visitante, quiero ingresar mi información personal para completar la compra.                      | El sistema debe proporcionar un formulario para ingresar nombre, apellido y demás datos personales.                           |
| FR006          | Gestionar direcciones         | Como cliente, quiero poder gestionar mis direcciones de envío para recibir mis pedidos.               | El sistema debe permitir agregar, editar y seleccionar direcciones de envío.                                                 |
| FR007          | Seleccionar dirección de envío| Como cliente, quiero seleccionar una dirección para el envío de mi pedido.                            | El sistema debe permitir seleccionar una dirección existente o ingresar una nueva para el envío.                               |
| FR008          | Ver costos de envío           | Como cliente, quiero ver los costos de envío basados en mi dirección.                                 | El sistema debe calcular y mostrar los costos de envío según la dirección seleccionada.                                        |
| FR009          | Ver impuestos aplicados       | Como cliente, quiero ver los impuestos aplicados a mi compra.                                         | El sistema debe calcular y mostrar el impuesto del 16% sobre el subtotal.                                                      |
| FR010          | Ver total de compra           | Como cliente, quiero ver el total de mi compra incluyendo productos, impuestos y envío.              | El sistema debe mostrar el desglose de costos y el total final a pagar.                                                        |
| FR011          | Ingresar información de pago  | Como cliente, quiero ingresar mi información de pago para completar la compra.                        | El sistema debe proporcionar un formulario seguro para ingresar datos de tarjeta y procesar el pago.                           |
| FR012          | Confirmar compra              | Como cliente, quiero recibir una confirmación de mi compra.                                           | El sistema debe mostrar una confirmación de compra exitosa y enviar detalles por correo electrónico.                          |

## 2. Especificaciones Técnicas

### 2.1 Backend/Laravel

#### Modelos de Datos:

- **Artículo (Item)**: Con las propiedades:
  - id
  - nombre
  - cantidad
  - precio

- **Cliente (Customer)**: Con las propiedades:
  - nombre
  - apellido
  - direcciones (relación con el modelo Address)

- **Dirección (Address)**: Con las propiedades:
  - línea 1
  - línea 2
  - ciudad
  - estado
  - código postal

- **Carrito (Cart)**: Puede tener un solo cliente, una dirección de envío y múltiples artículos.

#### Requisitos Adicionales:

- Tasa de impuesto del 16%
- Acceso a API de tarifas de envío (simular este servicio en el sistema)

#### Clases Requeridas:
Desarrollar al menos dos clases que permitan la configuración y recuperación de:
1. Nombre del cliente
2. Direcciones del cliente
3. Artículos en el carrito
4. Destino del envío
5. Costo de los artículos, incluyendo envío e impuestos
6. Subtotal y total de todos los artículos

### 2.2 Frontend/Vue

Desarrollar una interfaz donde los usuarios puedan:
- Agregar artículos
- Eliminar artículos
- Completar o seleccionar información del cliente
- Completar o seleccionar dirección de envío con código postal
- Usar un formulario de pago

### 2.3 Puntos Adicionales

- Añadir cobertura de pruebas
- Dividir el proyecto en:
  - Backend API REST
  - Frontend con Vue
- Conectar con API de pago
  - Tarjeta: 4111 1111 1111 1111
  - Titular: TEST (Éxito) o FAIL (Rechazado)
  - Fecha: Cualquier fecha posterior a hoy, por ejemplo: 12/29

### 2.4 Entrega

Para la entrega, debes crear un repositorio público en GitHub y compartir el enlace.