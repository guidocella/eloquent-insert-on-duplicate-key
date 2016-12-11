<?php

namespace InsertOnDuplicateKey;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AttachRecordsBuilder extends BelongsToMany
{
    /**
     * AttachRecordsBuilder constructor.
     *
     * @param string $foreignKey
     * @param mixed  $foreignKeyValue
     * @param string $otherKey
     */
    public function __construct($foreignKey, $foreignKeyValue, $otherKey)
    {
        $this->foreignKey = $foreignKey;
        $this->otherKey = $otherKey;

        $this->parent = new DummyModel(['id' => $foreignKeyValue]);
    }

    /**
     * Create an array of records to insert into the pivot table.
     *
     * @param  array $ids
     * @param  array $attributes
     * @return array
     */
    public function publicCreateAttachRecords($ids, array $attributes)
    {
        return $this->createAttachRecords($ids, $attributes);
    }
}
