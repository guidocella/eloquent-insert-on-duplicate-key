<?php

namespace InsertOnDuplicateKey;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InsertOnDuplicateKey\Models\Role;
use InsertOnDuplicateKey\Models\User;

class InsertOnDuplicateKeyTest extends InsertOnDuplicateKeyTestCase
{
    protected $updatedUser = [
        'id'    => 1,
        'name'  => 'new name 1',
        'email' => 'new1@gmail.com',
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
            ['id' => 1, 'name' => 'old', 'email' => 'old@gmail.com'],
            ['id' => 2, 'name' => 'old', 'email' => 'old@gmail.com'],
            ['id' => 3, 'name' => 'old', 'email' => 'old@gmail.com'],
        ]);

        Role::insert([
            ['id' => 1, 'name' => 'old'],
            ['id' => 2, 'name' => 'old'],
            ['id' => 3, 'name' => 'old'],
        ]);

        User::make(['id' => 1])->roles()->attach([
            1 => ['expires_at' => now()],
            2 => ['expires_at' => now()],
        ]);
    }

    public function testInsertOnDuplicateKey()
    {
        $updatedUser2 = [
            'id'    => 2,
            'name'  => 'new name 2',
            'email' => 'new2@gmail.com',
        ];

        User::insertOnDuplicateKey([$this->updatedUser, $updatedUser2]);

        $this->assertDatabaseHas('users', $this->updatedUser);
        $this->assertDatabaseHas('users', $updatedUser2);
    }

    public function testInsertOnDuplicateKeyPartialUpdate()
    {
        User::insertOnDuplicateKey([
            'id'    => 1,
            'name'  => 'new name 1',
            'email' => 'new1@gmail.com',
        ], ['name']);

        $this->assertDatabaseHas('users', ['id' => 1, 'name' => 'new name 1', 'email' => 'old@gmail.com']);
    }

    public function testInsertOnDuplicateKeyWithStringLiteralInOnDuplicateKeyUpdateClause()
    {
        User::insertOnDuplicateKey([
            [
                'id'    => 1,
                'name'  => 'created user',
                'email' => 'new1@gmail.com',
            ],
            [
                'id'    => 4,
                'name'  => 'created user',
                'email' => 'new4@gmail.com',
            ],
        ], ['name' => 'updated user']);

        $this->assertDatabaseHas('users', ['id' => 1, 'name' => 'updated user', 'email' => 'old@gmail.com']);
        $this->assertDatabaseHas('users', ['id' => 4, 'name' => 'created user', 'email' => 'new4@gmail.com']);
    }

    public function testInsertOnDuplicateKeyWithExpressionInOnDuplicateKeyUpdateClause()
    {
        User::insertOnDuplicateKey([
            [
                'id'   => 1,
                'name' => 'created user',
            ],
            [
                'id'   => 4,
                'name' => 'created user',
            ],
        ], ['name' => DB::raw('CONCAT(name, " updated user")')]);

        $this->assertDatabaseHas('users', ['id' => 1, 'name' => 'old updated user']);
        $this->assertDatabaseHas('users', ['id' => 4, 'name' => 'created user']);
    }

    public function testInsertOnDuplicateKeyWithMixed2ndArgumentArray()
    {
        User::insertOnDuplicateKey([
            [
                'id'    => 1,
                'name'  => 'created user',
                'email' => 'new1@gmail.com',
            ],
            [
                'id'    => 4,
                'name'  => 'created user',
                'email' => 'new4@gmail.com',
            ],
        ], ['name' => 'updated user', 'email']);

        $this->assertDatabaseHas('users', ['id' => 1, 'name' => 'updated user', 'email' => 'new1@gmail.com']);
        $this->assertDatabaseHas('users', ['id' => 4, 'name' => 'created user', 'email' => 'new4@gmail.com']);
    }

    public function testInsertIgnore()
    {
        $newUser = [
            'id'    => 4,
            'name'  => 'new name 4',
            'email' => null,
        ];

        User::insertIgnore([$this->updatedUser, $newUser]);

        $this->assertDatabaseMissing('users', $this->updatedUser);
        $this->assertDatabaseHas('users', $newUser);
    }

    public function testAttachOnDuplicateKey()
    {
        User::make(['id' => 1])->roles()->attachOnDuplicateKey([
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
        User::make(['id' => 1])->roles()->attachIgnore([
            1 => ['expires_at' => '2000-01-01 00:00:00'],
            3 => ['expires_at' => Carbon::tomorrow()],
        ]);

        // The row with user_id = 1 and role_id = 1
        // should have kept the initial value of expires_at = now().
        $this->assertDatabaseMissing('role_user', $this->updatedPivotRow);
        $this->assertDatabaseHas('role_user', [
            'user_id'    => 1,
            'role_id'    => 3,
            'expires_at' => Carbon::tomorrow(),
        ]);
    }
}
