<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // users.role is an ENUM('admin','waiter'); add 'owner' (MySQL / MariaDB)
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','waiter','owner') NOT NULL DEFAULT 'waiter'");
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            if (DB::table('users')->where('role', 'owner')->exists()) {
                throw new RuntimeException('Delete or change the owner account(s) before rolling this back.');
            }
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','waiter') NOT NULL DEFAULT 'waiter'");
        }
    }
};