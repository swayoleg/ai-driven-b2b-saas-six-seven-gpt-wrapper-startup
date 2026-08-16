<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waitlist_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('company')->nullable();
            $table->string('size')->nullable();
            $table->string('urgency')->nullable();
            $table->string('maturity')->nullable();
            $table->text('pain')->nullable();
            $table->string('budget')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->string('locale', 5)->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlist_submissions');
    }
};
