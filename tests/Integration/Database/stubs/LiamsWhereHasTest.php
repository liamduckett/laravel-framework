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
    }

    public function testWhereHasModelOnHasMany()
    {
        $post = new Post(['id' => 2]);

        $expected = 'select * from "users" where exists (select * from "posts" where "users"."id" = "posts"."user_id" and "posts"."id" = 2)';
        $actual = User::whereHas('posts', $post)->toRawSql();

        $this->assertEquals($expected, $actual);
    }
}

class User extends Model
{
    protected $guarded = [];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

class Post extends Model
{
    protected $guarded = [];
}
