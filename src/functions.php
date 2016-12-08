<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use InsertOnDuplicateKey\QueryBuilder;

if (!function_exists('insert_on_duplicate_key')) {
    /**
     * Run an insert on duplicate key update statement against the database.
     *
     * @param  Model|BelongsToMany $model
     * @param  array               $values
     * @return bool
     */
    function insert_on_duplicate_key($model, array $values)
    {
        $sql = 'INSERT INTO `' . $model->getTable() . '` (' . QueryBuilder::buildColumnList($values[0]) . ') VALUES '
            . QueryBuilder::buildQuestionMarks($values) . ' ON DUPLICATE KEY UPDATE ' . QueryBuilder::buildValueList($values[0]);

        return $model->getConnection()->insert($sql, array_flatten($values));
    }
}

if (!function_exists('insert_ignore')) {
    /**
     * Run an insert ignore statement against the database.
     *
     * @param  Model|BelongsToMany $model
     * @param  array               $values
     * @return bool
     */
    function insert_ignore($model, array $values)
    {
        $sql = 'INSERT IGNORE INTO `' . $model->getTable() . '` (' . QueryBuilder::buildColumnList($values[0]) . ') VALUES '
            . QueryBuilder::buildQuestionMarks($values);

        return $model->getConnection()->insert($sql, array_flatten($values));
    }
}

