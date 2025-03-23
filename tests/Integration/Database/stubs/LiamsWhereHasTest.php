<?php

namespace Illuminate\Tests\Integration\Database\LiamsWhereHasTest;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Tests\Integration\Database\DatabaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class LiamsWhereHasTest extends DatabaseTestCase
{
    protected function afterRefreshingDatabase()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
        });

        Schema::create('edits', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('post_id');
            $table->unsignedInteger('user_id');
        });
    }

    public function testWhereHasModelOnHasMany()
    {
        $post = new Post(['id' => 2]);

        $expected = 'select * from "users" where exists (select * from "posts" where "users"."id" = "posts"."user_id" and "posts"."id" = 2)';
        $actual = User::whereHas('posts', $post)->toRawSql();

        $this->assertEquals($expected, $actual);
    }

    public function testWhereHasModelOnBelongsTo()
    {
        $user = new User(['id' => 2]);

        $expected = 'select * from "posts" where exists (select * from "users" where "posts"."user_id" = "users"."id" and "users"."id" = 2)';
        $actual = Post::whereHas('user', $user)->toRawSql();

        $this->assertEquals($expected, $actual);
    }

    public function testWhereHasModelOnBelongsToMany()
    {
        $edit = new Edit(['id' => 2]);

        $expected = 'select * from "users" where exists (select * from "edits" where "posts"."user_id" = "users"."id" and "users"."id" = 2)';
        $actual = User::whereHas('edits', $edit)->toRawSql();

        $this->assertEquals($expected, $actual);
    }

    // TODO: do I need a test for HasOneThrough & HasManyThrough
}

class User extends Model
{
    protected $guarded = [];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * The roles that belong to the user.
     */
    public function edits()
    {
        return $this->belongsToMany(Edit::class);
    }
}

class Post extends Model
{
    protected $guarded = [];
}

class Edit extends Model
{
    protected $guarded = [];
}
