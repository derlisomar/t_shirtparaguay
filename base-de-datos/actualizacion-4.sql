-- =====================================================================
-- t_shirtparaguay · Actualización 4
-- Cambiar cantidades o quitar prendas de un pedido antes de finalizarlo.
--
-- Corré antes actualizacion-2.sql y actualizacion-3.sql si todavía no lo hiciste.
-- En pgAdmin: clic derecho en la base "tshirtparaguay" > Query Tool,
-- pegá todo este archivo y ejecutalo (F5). Se puede correr una sola vez.
-- (Si preferís, en vez de esto podés abrir ACTUALIZAR-TIENDA.bat)
-- =====================================================================
BEGIN;

-- Guarda cuántas unidades pidió el cliente cuando cambiás la cantidad
ALTER TABLE order_items ADD COLUMN original_quantity integer NULL;

INSERT INTO migrations (migration, batch)
VALUES ('2026_10_21_000003_ajuste_de_items_del_pedido', (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations));

COMMIT;
