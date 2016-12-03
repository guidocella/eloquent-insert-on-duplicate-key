<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use InsertOnDuplicateKey\QueryBuilder;

if (!function_exists('insert_on_duplicate_key')) {
    /**
     * Run an insert on duplicate key update statement against the database.
     *
     * @param  Model|BelongsToMany $model
     * @param  array               $data
     * @return bool
     */
    function insert_on_duplicate_key($model, array $data)
    {
        $sql = 'INSERT INTO `' . $model->getTable() . '` (' . QueryBuilder::buildColumnList($data[0]) . ') VALUES '
            . QueryBuilder::buildQuestionMarks($data) . ' ON DUPLICATE KEY UPDATE ' . QueryBuilder::buildValueList($data[0]);

        return $model->getConnection()->insert($sql, array_flatten($data));
    }
}

if (!function_exists('insert_ignore')) {
    /**
     * Run an insert ignore statement against the database.
     *
     * @param  Model|BelongsToMany $model
     * @param  array               $data
     * @return bool
     */
    function insert_ignore($model, array $data)
    {
        $sql = 'INSERT IGNORE INTO `' . $model->getTable() . '` (' . QueryBuilder::buildColumnList($data[0]) . ') VALUES '
            . QueryBuilder::buildQuestionMarks($data);

        return $model->getConnection()->insert($sql, array_flatten($data));
    }
}

