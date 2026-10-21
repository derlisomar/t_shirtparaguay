# t_shirtparaguay · tienda de ropa online

Tienda en **Laravel 12 + PHP 8.2 + PostgreSQL 13** con panel de administración, venta en el local y modo claro / oscuro.
Para instalarla en Windows seguí **COMO-INSTALAR.md**.

## La tienda

- **Slider del inicio** al estilo de la referencia: fondo de color con resplandor, prenda grande al centro, otra flotando atrás, precio con precio anterior tachado, botones de talle, frase en cursiva abajo y miniatura del siguiente slide. Pasa solo (se pausa con el mouse), con flechas, deslizando con el dedo y con el teclado. Todo se cambia desde el panel.
- **Catálogo** con filtros por categoría, talle, color, precio y ofertas, orden por precio o más vendidos, y buscador con resultados al instante.
- **Tarjetas** que muestran la segunda foto al pasar el mouse, etiqueta de descuento, «Nuevo», «Agotado», colores disponibles, favoritos y **agregar rápido** por talle.
- **Ficha de producto**: elegís color (cambian las fotos) y talle; muestra los talles agotados tachados y «¡Quedan solo 2!». Zoom en la foto, guía de talles, consulta por WhatsApp y barra fija de compra en el celular.
- **Carrito lateral** con barra de «te faltan Gs. X para el envío gratis».
- **Cuentas de clientes**: crear cuenta, ingresar, mis pedidos con el seguimiento del estado, mis datos y favoritos.
- **Checkout**: envío a domicilio o retiro en el local, transferencia o efectivo. Con transferencia el cliente puede **adjuntar el comprobante** (foto o PDF, opcional); también lo puede subir después desde *Mi cuenta* o mandarlo por WhatsApp.
- **Precio mayorista**: cada producto puede tener precio mayorista y cantidad mínima (por ejemplo, 6 o más remeras sumando talles y colores). El carrito lo aplica solo y avisa cuánto falta. Los clientes marcados como mayoristas ven y pagan siempre ese precio.
- **Modo claro y oscuro**: arranca según el celular o compu de la persona y se cambia con el botón de la luna.

## El panel (`/admin`)

- **Resumen**: muestra **solo pedidos confirmados** (los pendientes aparecen únicamente en «Pedidos por confirmar»). Ventas de hoy y del mes, finalizados del mes, y dos gráficos **por día (30 días) o por mes (12 meses)**: ventas realizadas en guaraníes y cantidad de pedidos confirmados, separando web y local. Más vendidos, últimos pedidos y poco stock.
- **Vender**: punto de venta para el local. Tocás la prenda, elegís talle y color, cliente registrado o de mostrador, forma de pago, descuento, vuelto y **precio mayorista** (se marca solo si el cliente es mayorista). Descuenta el stock en el momento y saca un comprobante para imprimir.
- **Pedidos**: pestañas **En curso**, **Finalizados**, Cancelados y Todos. Los pedidos de la web entran **Pendientes y no tocan el stock**. En el detalle ves el comprobante que adjuntó el cliente y el stock disponible de cada prenda; con **Procesar pedido** se descuenta el stock (y si querés, se marca el pago como recibido). Cuando el cliente recibió y el pago está conforme, **Confirmar entrega y pago** lo pasa a **Finalizados**. Estados: Pendiente → Procesado → Enviado → Entregado → Finalizado, o Cancelado. Cada pedido se imprime como **comprobante A4** (con firma de «recibí conforme») o como ticket. Cancelar o volver a Pendiente devuelve el stock. Mientras el pedido no esté finalizado podés **cambiar la cantidad o quitar prendas** (por si no tenés todo lo que pidió): el total se recalcula, el comprobante muestra lo que no se incluyó y, si el stock ya se había descontado, se ajusta solo. Filtro «Con comprobante» y botón de WhatsApp al cliente.
- **Clientes**: cargar clientes nuevos (el correo es opcional), editar sus datos, **cambiarles la contraseña**, eliminarlos (sus pedidos se conservan), notas internas, **límite por compra** (monto máximo de un pedido, en la web y en Vender) y la opción **Cliente mayorista**.
- **Productos**: nombre, descripción, precio, precio anterior, precio mayorista y desde cuántas unidades, categoría, talles y colores con una **grilla de stock** por combinación, varias fotos (cada una puede ir asociada a un color), visible / destacado / nuevo, duplicar.
- **Stock**: planilla para actualizar las cantidades de todo de una vez.
- **Categorías**, **Talles y colores**, **Slider del inicio** (con vista previa en vivo y orden arrastrando), **Clientes**, **Configuración** (logo, anuncio, envíos, datos bancarios, WhatsApp y redes) y **Usuarios del panel**.

## Logo

El logo «t_shirt PY» es de texto y se puede reemplazar subiendo una imagen en *Configuración* (también uno para modo oscuro y el ícono de la pestaña). En `public/img/` hay una versión PNG para redes (`logo-tshirtparaguay.png` y la blanca).

## Pago con tarjeta (segunda actualización)

Quedó preparado:
- Tabla `payments` que guarda cada intento de pago (pasarela, monto, estado, código de autorización, últimos 4 dígitos y la respuesta completa).
- La opción **«Tarjeta de crédito o débito – Próximamente»** ya aparece en el checkout, desactivada.
- `app/Payments/`: cada forma de cobro es una clase. `CardGateway.php` tiene marcados los dos lugares a completar (crear el pago y recibir el aviso).
- Rutas listas: `/pagos/card/retorno` y `/pagos/card/webhook` (este último sin protección CSRF, como piden las pasarelas).
- Para activarlo: completar `CardGateway.php` con Bancard vPOS, Pagopar u otro procesador y en `.env` poner `PAYMENTS_CARD_ENABLED=true` y las claves.

## Datos de ejemplo

15 productos (remeras, buzos, camperas inflables, joggers, shorts, gorras y un tote) con 46 fotos, 5 categorías, 6 talles, 18 colores y 4 slides. Las fotos de ejemplo son ilustraciones; reemplazalas por fotos reales desde el panel. Para el slider conviene usar fotos de la prenda **sin fondo** (PNG).

Para probar el resumen con ventas de ejemplo: en `.env` agregá `DEMO_ORDERS=true` y corré `php artisan migrate:fresh --seed` (**borra todo** lo cargado).

## Dónde está cada cosa

- `public/css/shop.css`: colores y estilos (las variables de arriba de todo definen los colores de los dos modos).
- `public/css/admin.css`, `public/js/shop.js`, `public/js/admin.js`
- `resources/views/shop`, `resources/views/admin`: pantallas
- `app/Http/Controllers` (tienda) y `app/Http/Controllers/Admin` (panel)
- `app/Support/Cart.php` (carrito y precio mayorista), `app/Support/OrderBuilder.php` (crea pedidos), `app/Models/Order.php` (procesar, cancelar y devolver stock)
- `app/Support/Receipts.php`: comprobantes de pago. Se guardan en `storage/app/private/comprobantes`, fuera de la parte pública, así solo los ven el cliente y el panel.
- `app/Payments/*`, `config/payments.php`: medios de pago
- `public/uploads/`: fotos subidas desde el panel (no hace falta `storage:link`)
- `database/migrations`, `database/seeders`, `base-de-datos/tshirtparaguay.sql` (base completa) y `base-de-datos/actualizacion-2.sql`, `actualizacion-3.sql` y `actualizacion-4.sql` (para actualizar una base que ya tenías)

## Publicarla en internet

Hace falta un hosting con PHP 8.2 y PostgreSQL y un dominio (por ejemplo `tshirtparaguay.com.py`). Antes de publicar: `APP_ENV=production`, `APP_DEBUG=false` y cambiar la contraseña del administrador.
