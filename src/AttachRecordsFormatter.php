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
        $this->foreignPivotKey = $this->foreignKey /* Laravel 5.4 */ = $foreignKey;
        $this->relatedPivotKey = $this->relatedKey /* Laravel 5.4 */ = $this->otherKey /* Laravel 5.3 */ = $relatedKey;

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
        $method = method_exists($this, 'formatAttachRecords')
            ? 'formatAttachRecords' // Laravel >= 5.4
            : 'createAttachRecords'; // Laravel 5.3

        return parent::$method($ids, $attributes);
    }
}
