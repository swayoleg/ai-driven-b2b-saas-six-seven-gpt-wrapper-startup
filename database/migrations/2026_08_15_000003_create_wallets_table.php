<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('network');
            $table->string('address');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            // The same address is legitimately reused across networks, so the
            // pair is what identifies a wallet (and what the seeder matches on).
            $table->unique(['address', 'network']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
