<?php


use App\Models\Report;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_devices_manufacture_params', function (Blueprint $table) {
            $table->id();
            $table->string('address');
            $table->string('param');
            $table->string('value');
            $table->foreignIdFor(Report::class)
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_devices_manufacture_params');
    }
};
