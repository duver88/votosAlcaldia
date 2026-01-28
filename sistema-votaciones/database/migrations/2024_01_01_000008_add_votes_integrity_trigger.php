<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Trigger que previene insertar un voto si ya hay mas votos que usuarios
        // Funciona como ultima linea de defensa a nivel de base de datos
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::unprepared('
                CREATE TRIGGER prevent_excess_votes
                BEFORE INSERT ON votes
                FOR EACH ROW
                BEGIN
                    DECLARE vote_count INT;
                    DECLARE user_count INT;
                    SELECT COUNT(*) INTO vote_count FROM votes;
                    SELECT COUNT(*) INTO user_count FROM users;
                    IF vote_count >= user_count THEN
                        SIGNAL SQLSTATE "45000"
                        SET MESSAGE_TEXT = "INTEGRITY: No se pueden registrar mas votos que usuarios";
                    END IF;
                END
            ');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS prevent_excess_votes');
        }
    }
};
