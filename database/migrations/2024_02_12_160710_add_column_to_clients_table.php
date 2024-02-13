<?php

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
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('main_user_id')->after('counter_number')->nullable();
            $table->foreignId('feedback_user_id')->after('counter_number')->nullable();
            $table->foreignId('content_user_id')->after('counter_number')->nullable();
            $table->foreignId('control_user_id')->after('counter_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('main_user_id');
            $table->dropColumn('feedback_user_id');
            $table->dropColumn('content_user_id');
            $table->dropColumn('control_user_id');
        });
    }
};
