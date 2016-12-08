<?php

namespace InsertOnDuplicateKey;

class QueryBuilder
{
    /**
     * Build the column list for a query.
     *
     * @param  array $first
     * @return string
     */
    public static function buildColumnList(array $first)
    {
        return '`' . implode('`,`', array_keys($first)) . '`';
    }

    /**
     * Build the question mark placeholders for an insert query.
     *
     * @param  array[] $values
     * @return string
     */
    public static function buildQuestionMarks(array $values)
    {
        $lines = '';
        $count = count($values[0]);

        foreach ($values as $row) {
            $questionMarks = '';

            for ($i = 0; $i < $count; ++$i) {
                $questionMarks .= '?,';
            }

            $lines .= '(' . rtrim($questionMarks, ',') . '),';
        }

        return rtrim($lines, ',');
    }

    /**
     * Build a value list for an insert on duplicate key update query.
     *
     * @param  array $first
     * @return string
     */
    public static function buildValueList(array $first)
    {
        $list = '';

        foreach (array_keys($first) as $key) {
            $list .= "`$key` = VALUES(`$key`),";
        }

        return rtrim($list, ',');
    }
}
