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
        Schema::table('post_tag', function (Blueprint $table) {
            //
        });

        Schema::rename('tag_post' , 'post_tag');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_tag', function (Blueprint $table) {
            //
        });
    }
};
