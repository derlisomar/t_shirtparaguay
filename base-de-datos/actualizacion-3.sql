-- =====================================================================
-- t_shirtparaguay · Actualización 3
-- Clientes desde el panel, límite de compra y pedidos finalizados.
--
-- Corré antes actualizacion-2.sql si todavía no lo hiciste.
-- En pgAdmin: clic derecho en la base "tshirtparaguay" > Query Tool,
-- pegá todo este archivo y ejecutalo (F5). Se puede correr una sola vez.
-- (Si preferís, en vez de esto podés abrir ACTUALIZAR-TIENDA.bat)
-- =====================================================================
BEGIN;

ALTER TABLE users ALTER COLUMN email DROP NOT NULL;
ALTER TABLE users ADD COLUMN purchase_limit bigint NULL;
ALTER TABLE users ADD COLUMN admin_notes text NULL;
ALTER TABLE orders ADD COLUMN finished_at timestamp(0) without time zone NULL;

-- Lo que ya estaba entregado y pagado pasa a «Finalizado»
UPDATE orders SET status = 'finalizado', finished_at = updated_at WHERE status = 'entregado' AND payment_status = 'pagado';

INSERT INTO migrations (migration, batch)
VALUES ('2026_10_21_000002_clientes_limite_y_pedidos_finalizados', (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations));

COMMIT;
