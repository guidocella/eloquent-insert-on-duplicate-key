<?php

namespace InsertOnDuplicateKey;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class InsertOnDuplicateKeyServiceProvider extends ServiceProvider
{
    /**
     * Register the insert macros.
     */
    public function boot()
    {
        /**
         * Run an insert ignore statement against the database.
         *
         * @param  array       $values
         * @param  string|null $pivotTable
         * @return bool
         */
        Builder::macro('insertIgnore', function (array $values, $pivotTable = null) {
            return $this->insertOnDuplicateKey($values, 'ignore', $pivotTable);
        });

        /**
         * Run an insert on duplicate key update statement against the database.
         *
         * @param  array       $values
         * @param  string      $type
         * @param  string|null $pivotTable
         * @return bool
         */
        Builder::macro('insertOnDuplicateKey', function (array $values, $type = 'on duplicate key', $pivotTable = null) {
            // Since every insert gets treated like a batch insert, we will make sure the
            // bindings are structured in a way that is convenient for building these
            // inserts statements by verifying the elements are actually an array.
            if (empty($values)) {
                return true;
            }

            if (!is_array(reset($values))) {
                $values = [$values];
            }

            // Here, we will sort the insert keys for every record so that each insert is
            // in the same order for the record. We need to make sure this is the case
            // so there are not any errors or problems when inserting these records.
            else {
                foreach ($values as $key => $value) {
                    ksort($value);
                    $values[$key] = $value;
                }
            }

            // Finally, we will run this query against the database connection and return
            // the results. We will need to also flatten these bindings before running
            // the query so they are all in one huge, flattened array for execution.
            $bindings = $this->cleanBindings(Arr::flatten($values, 1));

            // Essentially we will force every insert to be treated as a batch insert which
            // simply makes creating the SQL easier for us since we can utilize the same
            // basic routine regardless of an amount of records given to us to insert.
            $table = $this->grammar->wrapTable($pivotTable ?: $this->from);

            if (! is_array(reset($values))) {
                $values = [$values];
            }

            $columns = array_keys(reset($values));

            $columnsString = $this->grammar->columnize($columns);

            // We need to build a list of parameter place-holders of values that are bound
            // to the query. Each insert should have the exact same amount of parameter
            // bindings so we will loop through the record and parameterize them all.
            $parameters = collect($values)->map(function ($record) {
                return '('.$this->grammar->parameterize($record).')';
            })->implode(', ');

            $sql = 'insert ' . ($type === 'ignore' ? 'ignore ' : '') . "into $table ($columnsString) values $parameters";

            if ($type === 'ignore') {
                return $this->connection->insert($sql, $bindings);
            }

            $sql .= ' on duplicate key update ';

            foreach ($columns as $column) {
                $column = $this->grammar->wrap($column);

                $sql .= "$column = VALUES($column),";
            }

            return $this->connection->insert(rtrim($sql, ','), $bindings);
        });

        /**
         * Attach models to the parent ignoring existing associations.
         *
         * @param  mixed $id
         * @param  array $attributes
         * @return bool
         */
        Builder::macro('attachIgnore', function ($id, array $attributes = []) {
            $this->attachOnDuplicateKey($id, $attributes, 'ignore');
        });

        /**
         * Attach models to the parent updating existing associations.
         *
         * @param  mixed  $id
         * @param  array  $attributes
         * @param  string $type
         * @return bool
         */
        Builder::macro('attachOnDuplicateKey', function ($id, array $attributes = [], $type = 'on duplicate key') {
            if ($id instanceof Model) {
                $id = $id->getKey();
            }

            if ($id instanceof Collection) {
                $id = $id->modelKeys();
            }

            $foreignKey = $this->wheres[0]['column'];
            $foreignKeyValue = $this->wheres[0]['value'];
            $relatedKey = $this->joins[0]->wheres[0]['second'];

            $attachRecordsBuilder = new AttachRecordsFormatter($foreignKey, $foreignKeyValue, $relatedKey);

            $this->insertOnDuplicateKey(
                $attachRecordsBuilder->formatAttachRecords((array)$id, $attributes),
                $type,
                $this->joins[0]->table
            );
        });
    }
}
