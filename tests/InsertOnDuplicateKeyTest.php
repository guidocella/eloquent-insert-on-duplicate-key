<?php

namespace InsertOnDuplicateKey;

use Carbon\Carbon;
use InsertOnDuplicateKey\Models\Role;
use InsertOnDuplicateKey\Models\User;

class InsertOnDuplicateKeyTest extends EloquentTestCase
{
    /**
     * Seed the database.
     */
    public function setUp()
    {
        parent::setUp();

        $attachRecords = [];

        for ($i = 1; $i <= 3; $i++) {
            User::create(['name' => 'foo']);
            Role::create(['name' => 'foo']);

            $attachRecords[$i] = ['expires_at' => Carbon::now()];
        }

        (new User(['id' => 1]))->roles()->attach([
            1 => ['expires_at' => Carbon::now()],
            2 => ['expires_at' => Carbon::now()],
        ]);
    }

    public function testInsertOnDuplicateKey_throughModel()
    {
        $user1 = [
            'id'   => 1,
            'name' => 'User One',
        ];

        $user2 = [
            'id'   => 2,
            'name' => 'User Two',
        ];

        insert_on_duplicate_key(new User, [$user1, $user2]);

        $this->seeInDatabase('users', $user1);
        $this->seeInDatabase('users', $user2);
    }

    public function testInsertOnDuplicateKey_intoPivot()
    {
        $pivotRow1 = [
            'user_id'    => 1,
            'role_id'    => 1,
            'expires_at' => Carbon::today(),
        ];

        $pivotRow2 = [
            'user_id'    => 1,
            'role_id'    => 2,
            'expires_at' => Carbon::tomorrow(),
        ];

        insert_on_duplicate_key((new User())->roles(), [$pivotRow1, $pivotRow2]);

        $this->seeInDatabase('role_user', $pivotRow1);
        $this->seeInDatabase('role_user', $pivotRow2);
    }

    public function testInsertIgnore_throughModel()
    {
        $user1 = [
            'id'   => 1,
            'name' => 'User One',
        ];

        $user2 = [
            'id'   => 4,
            'name' => 'User Two',
        ];

        insert_ignore(new User, [$user1, $user2]);

        $this->dontSeeInDatabase('users', $user1);
        $this->seeInDatabase('users', $user2);
    }

    public function testInsertIgnore_intoPivot()
    {
        $pivotRow1 = [
            'user_id'    => 1,
            'role_id'    => 1,
            'expires_at' => Carbon::today(),
        ];

        $pivotRow2 = [
            'user_id'    => 1,
            'role_id'    => 3,
            'expires_at' => Carbon::tomorrow(),
        ];

        insert_ignore((new User())->roles(), [$pivotRow1, $pivotRow2]);

        $this->dontSeeInDatabase('role_user', $pivotRow1);
        $this->seeInDatabase('role_user', $pivotRow2);
    }
}
