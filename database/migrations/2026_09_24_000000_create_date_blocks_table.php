<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('date_blocks', function (Blueprint $table): void {
            $table->id();
            $table->date('entry_date');
            $table->date('out_date');
            $table->string('reason', 500);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['entry_date', 'out_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('date_blocks');
    }
};
