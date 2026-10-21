# Cómo instalar t_shirtparaguay en tu computadora (Windows + XAMPP)

Ya tenés PHP 8.2 (XAMPP) y PostgreSQL 13 andando, así que son 5 pasos. No hace falta Composer ni Node: el zip ya trae todo.

## 1. Activar PostgreSQL en el PHP de XAMPP (una sola vez)

1. Abrí con el Bloc de notas el archivo `C:\xampp\php\php.ini`.
2. Buscá (Ctrl + B) estas dos líneas y **borrales el punto y coma** del principio:
   ```
   ;extension=pdo_pgsql   →   extension=pdo_pgsql
   ;extension=pgsql       →   extension=pgsql
   ```
3. Revisá que estas también estén sin punto y coma: `extension=fileinfo`, `extension=mbstring`, `extension=openssl`.
4. Guardá. Si el Apache de XAMPP estaba prendido, reinicialo (no es obligatorio, la tienda no usa Apache).

## 2. Crear la base de datos

1. Abrí **pgAdmin**.
2. A la izquierda: *Servers → PostgreSQL 13*, clic derecho en **Databases → Create → Database…**
3. Nombre: `tshirtparaguay` → **Save**.

## 3. Descomprimir la tienda

Descomprimí `tshirtparaguay.zip` en `C:\tiendas\`. Te queda la carpeta `C:\tiendas\tshirtparaguay` con todo adentro (incluida la carpeta `vendor`).

## 4. Poner tu contraseña de PostgreSQL

1. En la carpeta de la tienda, abrí el archivo `.env` con el Bloc de notas (clic derecho → Abrir con → Bloc de notas).
2. Buscá la línea `DB_PASSWORD=` y poné tu contraseña de PostgreSQL pegada, por ejemplo `DB_PASSWORD=miclave123`.
3. Guardá y cerrá.

## 5. Cargar la base y abrir la tienda

1. Doble clic en **`INSTALAR-BASE-DE-DATOS.bat`**. Crea las tablas y carga los productos de ejemplo. Tiene que terminar diciendo «Listo».
2. Doble clic en **`INICIAR-TIENDA.bat`**. Se abre el navegador en **http://localhost:8000**.

El panel está en **http://localhost:8000/admin**
- Usuario: `admin@tshirtparaguay.com`
- Contraseña: `admin123` → cambiala en *Usuarios del panel* (poné tu mismo correo con la contraseña nueva).

> **Otra forma de cargar la base (en lugar del paso 5.1):** en pgAdmin, clic derecho sobre la base `tshirtparaguay` → **Query Tool** → botón de abrir archivo → elegí `base-de-datos/tshirtparaguay.sql` → **Ejecutar** (F5).

## Si ya tenías instalada una versión anterior

Las actualizaciones mantienen tus productos, clientes y pedidos.

1. Copiá los archivos de `tshirtparaguay-actualizacion-4.zip` **encima** de tu carpeta de la tienda (aceptá **reemplazar**). Ese zip no trae el `.env`, así que tu contraseña queda igual.
2. Actualizá la base con **una** de estas dos formas:
   - **Lo más fácil:** doble clic en **`ACTUALIZAR-TIENDA.bat`**. Detecta solo qué le falta a tu base y lo aplica.
   - **Con pgAdmin:** clic derecho en la base `tshirtparaguay` → **Query Tool** → ejecutá (F5) cada archivo que te falte, **en orden y una sola vez**:
     - `base-de-datos/actualizacion-2.sql` (comprobante, stock al procesar, gráficos y mayorista). Si ya lo corriste, salteálo.
     - `base-de-datos/actualizacion-3.sql` (clientes desde el panel, límite de compra y pedidos finalizados). Si ya lo corriste, salteálo.
     - `base-de-datos/actualizacion-4.sql` (cambiar cantidades o quitar prendas de un pedido).
3. Listo. Lo que ya estaba entregado y pagado aparece en **Pedidos → Finalizados**.

## Para usarla otro día

Solo doble clic en `INICIAR-TIENDA.bat`. Mientras esa ventana negra esté abierta, la tienda funciona.

## Si aparece un error

| Mensaje | Qué hacer |
|---|---|
| `could not find driver` | Falta el paso 1 (activar `pdo_pgsql` y `pgsql` en `php.ini`). |
| `password authentication failed` | La contraseña del `.env` no es la de PostgreSQL. Corregila y repetí el paso 5. |
| `database "tshirtparaguay" does not exist` | Falta el paso 2. |
| `"php" no se reconoce…` | XAMPP no está en `C:\xampp`. Editá los `.bat` y cambiá `C:\xampp\php\php.exe` por la ruta donde lo tengas. |
| `Failed to listen on 127.0.0.1:8000` | Ya hay otra tienda abierta en ese puerto. Cerrá la otra ventana negra. |

Si te sale otro error, sacale una captura a la ventana negra y mandala por acá.
