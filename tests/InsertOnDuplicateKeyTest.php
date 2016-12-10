<?php

namespace InsertOnDuplicateKey;

use Carbon\Carbon;
use InsertOnDuplicateKey\Models\Role;
use InsertOnDuplicateKey\Models\User;

class InsertOnDuplicateKeyTest extends InsertOnDuplicateKeyTestCase
{
    protected $user1 = [
        'id'   => 1,
        'name' => 'User One',
    ];

    protected $pivotRow1 = [
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
        $user2 = [
            'id'   => 2,
            'name' => 'User Two',
        ];

        insert_on_duplicate_key(new User, [$this->user1, $user2]);

        $this->seeInDatabase('users', $this->user1);
        $this->seeInDatabase('users', $user2);
    }

    public function testInsertOnDuplicateKey_intoPivot()
    {
        $pivotRow2 = [
            'user_id'    => 1,
            'role_id'    => 2,
            'expires_at' => Carbon::tomorrow(),
        ];


        insert_on_duplicate_key((new User())->roles(), [$this->pivotRow1, $pivotRow2]);

        $this->seeInDatabase('role_user', $this->pivotRow1);
        $this->seeInDatabase('role_user', $pivotRow2);
    }

    public function testInsertIgnore_throughModel()
    {
        $user2 = [
            'id'   => 4,
            'name' => 'User Two',
        ];

        insert_ignore(new User, [$this->user1, $user2]);

        $this->dontSeeInDatabase('users', $this->user1);
        $this->seeInDatabase('users', $user2);
    }

    public function testInsertIgnore_intoPivot()
    {
        $pivotRow2 = [
            'user_id'    => 1,
            'role_id'    => 3,
            'expires_at' => Carbon::tomorrow(),
        ];

        insert_ignore((new User())->roles(), [$this->pivotRow1, $pivotRow2]);

        $this->dontSeeInDatabase('role_user', $this->pivotRow1);
        $this->seeInDatabase('role_user', $pivotRow2);
    }
}
