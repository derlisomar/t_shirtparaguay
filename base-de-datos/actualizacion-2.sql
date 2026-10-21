-- =====================================================================
-- t_shirtparaguay · Actualización 2
-- Comprobante de pago, stock al procesar, dashboard y precio mayorista.
--
-- Usalo SOLO si ya tenés la base creada con la versión anterior.
-- En pgAdmin: clic derecho en la base "tshirtparaguay" > Query Tool,
-- pegá todo este archivo y ejecutalo (F5). Se puede correr una sola vez.
-- (Si preferís, en vez de esto podés ejecutar: php artisan migrate)
-- =====================================================================
BEGIN;

ALTER TABLE orders ADD COLUMN receipt_path varchar(255) NULL;
ALTER TABLE orders ADD COLUMN receipt_uploaded_at timestamp(0) without time zone NULL;
ALTER TABLE orders ADD COLUMN stock_applied boolean NOT NULL DEFAULT false;
ALTER TABLE orders ADD COLUMN processed_at timestamp(0) without time zone NULL;
ALTER TABLE orders ADD COLUMN is_wholesale boolean NOT NULL DEFAULT false;
CREATE INDEX orders_processed_at_index ON orders (processed_at);

-- Los pedidos que ya existían descontaron stock al crearse
UPDATE orders SET stock_applied = true;
UPDATE orders SET processed_at = created_at WHERE status <> 'cancelado';
UPDATE orders SET status = 'procesado' WHERE status IN ('confirmado', 'preparando');

ALTER TABLE order_items ADD COLUMN is_wholesale boolean NOT NULL DEFAULT false;
ALTER TABLE products ADD COLUMN wholesale_price bigint NULL;
ALTER TABLE products ADD COLUMN wholesale_min_qty integer NULL;
ALTER TABLE users ADD COLUMN is_wholesale boolean NOT NULL DEFAULT false;

INSERT INTO migrations (migration, batch)
VALUES ('2026_10_21_000001_pedidos_comprobante_y_mayorista', (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations));

COMMIT;
