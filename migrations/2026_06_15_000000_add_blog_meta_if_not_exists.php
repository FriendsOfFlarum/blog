<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        if (!$schema->hasTable('blog_meta')) {
            $schema->create('blog_meta', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('discussion_id')->unsigned();
                $table->string('featured_image')->nullable();
                $table->text('summary')->nullable();
                $table->boolean('is_featured')->nullable()->default(false);
                $table->boolean('is_sized')->nullable()->default(false);
                $table->boolean('is_pending_review')->nullable()->default(false);

                $table->foreign('discussion_id')->references('id')->on('discussions')->onDelete('cascade');
            });
        }
    },
    'down' => function (Builder $schema) {
        $schema->drop('blog_meta');
    },
];
