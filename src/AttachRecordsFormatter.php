<?php

namespace InsertOnDuplicateKey;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AttachRecordsFormatter extends BelongsToMany
{
    /**
     * AttachRecordsFormatter constructor.
     *
     * @param string $foreignKey
     * @param mixed  $foreignKeyValue
     * @param string $relatedKey
     */
    public function __construct($foreignKey, $foreignKeyValue, $relatedKey)
    {
        $this->foreignKey = $foreignKey;
        $this->relatedKey = $this->otherKey = $relatedKey;
        // $otherKey was used in Laravel <=5.3.

        $this->parent = new DummyModel(['id' => $foreignKeyValue]);
    }

    /**
     * Create an array of records to insert into the pivot table.
     *
     * @param  array $ids
     * @param  array $attributes
     * @return array
     */
    public function formatAttachRecords($ids, array $attributes)
    {
        $method = method_exists($this, 'createAttachRecords') ? 'createAttachRecords' : 'formatAttachRecords';
        // createAttachRecords was used in Laravel <=5.3.

        return parent::$method($ids, $attributes);
    }
}
