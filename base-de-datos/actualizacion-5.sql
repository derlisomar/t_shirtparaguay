-- =====================================================================
-- t_shirtparaguay · Actualización 5
-- Líneas dentro de las categorías y cuentas con nombre de usuario.
--
-- Corré antes actualizacion-2, 3 y 4 si todavía no lo hiciste.
-- En pgAdmin: clic derecho en la base "tshirtparaguay" > Query Tool,
-- pegá todo este archivo y ejecutalo (F5). Se puede correr una sola vez.
-- (Si preferís, en vez de esto podés abrir ACTUALIZAR-TIENDA.bat)
-- =====================================================================
BEGIN;

-- Una categoría puede ir dentro de otra (Remeras > Oversize 30.1)
ALTER TABLE categories ADD COLUMN parent_id bigint NULL;
ALTER TABLE categories ADD CONSTRAINT categories_parent_id_foreign
  FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE SET NULL;

-- Clientes: usuario para entrar (sin correo), apellido y país
ALTER TABLE users ADD COLUMN username varchar(40) NULL;
ALTER TABLE users ADD CONSTRAINT users_username_unique UNIQUE (username);
ALTER TABLE users ADD COLUMN last_name varchar(120) NULL;
ALTER TABLE users ADD COLUMN country varchar(60) NULL;

INSERT INTO migrations (migration, batch)
VALUES ('2026_10_22_000001_lineas_y_cuentas_por_usuario', (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations));

COMMIT;
