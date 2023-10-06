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
        Schema::create('reach_goals', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('param');
            $table->string('value');
            $table->string('type');
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
        Schema::dropIfExists('reach_goals');
    }
};
