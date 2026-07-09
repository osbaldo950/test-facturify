<?php

use App\Enums\ThreadStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default(ThreadStatus::Open->value)->index();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();

            $table->index('subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('threads');
    }
};
