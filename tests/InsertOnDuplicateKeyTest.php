<?php

namespace InsertOnDuplicateKey;

use Carbon\Carbon;
use InsertOnDuplicateKey\Models\Role;
use InsertOnDuplicateKey\Models\User;

class InsertOnDuplicateKeyTest extends InsertOnDuplicateKeyTestCase
{
    protected $updatedUser = [
        'id'   => 1,
        'name' => 'User 1',
        'email' => 'foo1@gmail.com'
    ];

    protected $updatedPivotRow = [
        'user_id'    => 1,
        'role_id'    => 1,
        'expires_at' => '2000-01-01 00:00:00',
    ];

    /**
     * Seed the database.
     */
    protected function setUp()
    {
        parent::setUp();

        User::insert([
            ['id' => 1, 'name' => 'foo', 'email' => 'foo1@gmail.com'],
            ['id' => 2, 'name' => 'foo', 'email' => 'foo2@gmail.com'],
            ['id' => 3, 'name' => 'foo', 'email' => 'foo3@gmail.com'],
        ]);

        Role::insert([
            ['id' => 1, 'name' => 'foo'],
            ['id' => 2, 'name' => 'foo'],
            ['id' => 3, 'name' => 'foo'],
        ]);

        (new User(['id' => 1]))->roles()->attach([
            1 => ['expires_at' => Carbon::now()],
            2 => ['expires_at' => Carbon::now()],
        ]);
    }

    public function testInsertOnDuplicateKey()
    {
        $updatedUser2 = [
            'id'   => 2,
            'name' => 'User 2',
            'email' => 'foo2@gmail.com'
        ];

        User::insertOnDuplicateKey([$this->updatedUser, $updatedUser2]);

        $this->assertDatabaseHas('users', $this->updatedUser);
        $this->assertDatabaseHas('users', $updatedUser2);
    }

    public function testInsertOnDuplicateKeyFullUpdate()
    {
        $updatedUser = [
            'id'   => 1,
            'email' => 'bar@gmail.com',
            'name' => 'Bar'
        ];

        User::insertOnDuplicateKey([$updatedUser]);

        $updated_user = User::find(1);
        $this->assertEquals('Bar',$updated_user->name);
        $this->assertEquals('bar@gmail.com',$updated_user->email);
    }

    public function testInsertOnDuplicateKeyPartialUpdate()
    {
        $updatedUser = [
            'id'   => 1,
            'email' => 'bar@gmail.com',
            'name' => 'Bar'
        ];

        User::insertOnDuplicateKey([$this->updatedUser, $updatedUser],['name']);

        $updated_user = User::find(1);
        $this->assertEquals('Bar',$updated_user->name);
        $this->assertEquals('foo1@gmail.com',$updated_user->email);
    }

    public function testInsertIgnore()
    {
        $newUser = [
            'id'   => 4,
            'name' => 'User 2',
            'email' => null
        ];

        User::insertIgnore([$this->updatedUser, $newUser]);

        $this->assertDatabaseMissing('users', $this->updatedUser);
        $this->assertDatabaseHas('users', $newUser);
    }

    public function testAttachOnDuplicateKey()
    {
        (new User(['id' => 1]))->roles()->attachOnDuplicateKey([
            1 => ['expires_at' => '2000-01-01 00:00:00'],
            2 => ['expires_at' => Carbon::tomorrow()],
        ]);

        $this->assertDatabaseHas('role_user', $this->updatedPivotRow);
        $this->assertDatabaseHas('role_user', [
            'user_id'    => 1,
            'role_id'    => 2,
            'expires_at' => Carbon::tomorrow(),
        ]);
    }

    public function testAttachIgnore()
    {
        (new User(['id' => 1]))->roles()->attachIgnore([
            1 => ['expires_at' => '2000-01-01 00:00:00'],
            3 => ['expires_at' => Carbon::tomorrow()],
        ]);

        $this->assertDatabaseMissing('role_user', $this->updatedPivotRow);
        $this->assertDatabaseHas('role_user', [
            'user_id'    => 1,
            'role_id'    => 3,
            'expires_at' => Carbon::tomorrow(),
        ]);
    }
}
