<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Trigger for new user registration - notify all sellers
        DB::unprepared('
            CREATE TRIGGER notify_sellers_on_user_registration
            AFTER INSERT ON users
            FOR EACH ROW
            BEGIN
                INSERT INTO notifications (user_id, type, title, message, data, priority, expires_at, created_at, updated_at)
                SELECT
                    id,
                    "user_registration",
                    "New User Registered",
                    CONCAT("A new user ", NEW.name, " has registered to the platform."),
                    JSON_OBJECT("user_id", NEW.id, "user_name", NEW.name, "user_email", NEW.email),
                    "low",
                    DATE_ADD(NOW(), INTERVAL 7 DAY),
                    NOW(),
                    NOW()
                FROM users
                WHERE role = "seller";
            END
        ');

        // Trigger for new order - notify all sellers
        DB::unprepared('
            CREATE TRIGGER notify_sellers_on_new_order
            AFTER INSERT ON orders
            FOR EACH ROW
            BEGIN
                INSERT INTO notifications (user_id, type, title, message, data, priority, expires_at, action_url, created_at, updated_at)
                SELECT
                    id,
                    "new_order",
                    "New Order Placed",
                    CONCAT("A new order #", NEW.order_number, " has been placed for Tsh ", FORMAT(NEW.total_amount, 0), "."),
                    JSON_OBJECT("order_id", NEW.id, "order_number", NEW.order_number, "total_amount", NEW.total_amount, "customer_id", NEW.user_id),
                    "medium",
                    DATE_ADD(NOW(), INTERVAL 3 DAY),
                    CONCAT("/seller/orders/", NEW.id),
                    NOW(),
                    NOW()
                FROM users
                WHERE role = "seller";
            END
        ');

        // Trigger for low stock products - notify all sellers when stock <= 5
        DB::unprepared('
            CREATE TRIGGER notify_sellers_on_low_stock
            AFTER UPDATE ON products
            FOR EACH ROW
            BEGIN
                IF NEW.stock <= 5 AND (OLD.stock > 5 OR OLD.stock IS NULL) THEN
                    INSERT INTO notifications (user_id, type, title, message, data, priority, expires_at, action_url, created_at, updated_at)
                    SELECT
                        id,
                        "low_stock",
                        "Product Low Stock Alert",
                        CONCAT("Product \"", NEW.name, "\" is running low on stock (", NEW.stock, " remaining)."),
                        JSON_OBJECT("product_id", NEW.id, "product_name", NEW.name, "current_stock", NEW.stock),
                        "high",
                        DATE_ADD(NOW(), INTERVAL 1 DAY),
                        CONCAT("/seller/my-store?product=", NEW.id),
                        NOW(),
                        NOW()
                    FROM users
                    WHERE role = "seller";
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS notify_sellers_on_user_registration');
        DB::unprepared('DROP TRIGGER IF EXISTS notify_sellers_on_new_order');
        DB::unprepared('DROP TRIGGER IF EXISTS notify_sellers_on_low_stock');
    }
};
