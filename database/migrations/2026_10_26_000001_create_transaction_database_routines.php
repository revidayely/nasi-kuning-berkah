<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_transaction_line_subtotal(p_quantity INT UNSIGNED, p_unit_price INT UNSIGNED)
            RETURNS BIGINT UNSIGNED
            DETERMINISTIC
            NO SQL
            RETURN p_quantity * p_unit_price
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_transaction_details_before_insert
            BEFORE INSERT ON transaction_details
            FOR EACH ROW
            SET NEW.subtotal = fn_transaction_line_subtotal(NEW.quantity, NEW.unit_price)
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_transaction_details_before_update
            BEFORE UPDATE ON transaction_details
            FOR EACH ROW
            SET NEW.subtotal = fn_transaction_line_subtotal(NEW.quantity, NEW.unit_price)
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_transaction_summary(IN p_transaction_id BIGINT UNSIGNED)
            BEGIN
                SELECT
                    t.id AS transaction_id,
                    t.customer_name,
                    t.status,
                    t.payment_status,
                    t.total_amount AS recorded_total,
                    COALESCE(SUM(td.subtotal), 0) AS calculated_total,
                    t.created_at
                FROM transactions AS t
                LEFT JOIN transaction_details AS td ON td.transaction_id = t.id
                WHERE t.id = p_transaction_id
                GROUP BY t.id, t.customer_name, t.status, t.payment_status, t.total_amount, t.created_at;

                SELECT
                    td.menu_id,
                    m.name AS menu_name,
                    td.quantity,
                    td.unit_price,
                    td.subtotal
                FROM transaction_details AS td
                INNER JOIN menus AS m ON m.id = td.menu_id
                WHERE td.transaction_id = p_transaction_id
                ORDER BY td.id;
            END
        SQL);
    }

    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_transaction_summary');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_transaction_details_before_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_transaction_details_before_insert');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_transaction_line_subtotal');
    }
};