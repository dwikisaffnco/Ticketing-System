<?php

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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notify_email_on_ticket_created')->default(true)->after('last_login_at');
            $table->boolean('notify_email_on_ticket_reply')->default(true)->after('notify_email_on_ticket_created');
            $table->boolean('notify_email_on_ticket_closed')->default(true)->after('notify_email_on_ticket_reply');
            $table->boolean('notify_email_on_ticket_updated')->default(true)->after('notify_email_on_ticket_closed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'notify_email_on_ticket_created',
                'notify_email_on_ticket_reply',
                'notify_email_on_ticket_closed',
                'notify_email_on_ticket_updated',
            ]);
        });
    }
};
