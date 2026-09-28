<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
                $table->string('name', 255);
                $table->string('email', 255)->unique();
                $table->string('password', 255);
                $table->string('documento', 30)->nullable()->index();
                $table->string('telefono', 50)->nullable();
                $table->string('rol', 30)->default('operador')->index(); // superadmin, admin, operador, consulta
                $table->boolean('activo')->default(true)->index();
                $table->timestamp('ultimo_acceso')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        } else {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'empresa_id')) {
                    $table->foreignId('empresa_id')->nullable()->after('id')->constrained('empresas')->nullOnDelete();
                }
                if (!Schema::hasColumn('users', 'documento')) {
                    $table->string('documento', 30)->nullable()->after('password')->index();
                }
                if (!Schema::hasColumn('users', 'telefono')) {
                    $table->string('telefono', 50)->nullable()->after('documento');
                }
                if (!Schema::hasColumn('users', 'rol')) {
                    $table->string('rol', 30)->default('operador')->after('telefono')->index();
                }
                if (!Schema::hasColumn('users', 'activo')) {
                    $table->boolean('activo')->default(true)->after('rol')->index();
                }
                if (!Schema::hasColumn('users', 'ultimo_acceso')) {
                    $table->timestamp('ultimo_acceso')->nullable()->after('activo');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'empresa_id')) {
                    $table->dropForeign(['empresa_id']);
                    $table->dropColumn('empresa_id');
                }
                $columns = ['documento', 'telefono', 'rol', 'activo', 'ultimo_acceso'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
