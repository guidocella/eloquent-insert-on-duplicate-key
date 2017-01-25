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

        $data = [
            ['id' => 1, 'name' => 'foo'],
            ['id' => 2, 'name' => 'foo'],
            ['id' => 3, 'name' => 'foo'],
        ];

        User::insert($data);
        Role::insert($data);

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
        ];

        User::insertOnDuplicateKey([$this->updatedUser, $updatedUser2]);

        $this->assertDatabaseHas('users', $this->updatedUser);
        $this->assertDatabaseHas('users', $updatedUser2);
    }

    public function testInsertIgnore()
    {
        $newUser = [
            'id'   => 4,
            'name' => 'User 2',
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
