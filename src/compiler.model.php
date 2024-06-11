<?php

namespace SCSSWrapper\Model;

class Compiler
{
    private string $location = '';
    private \SQLite3 $connection;

    /**
     * Constructor
     * @param array<mixed> $options
     */
    public function __construct(array $options = [])
    {
        $this->location = isset($options['db_location']) ? $options['db_location'] : '';
        if (!empty($this->location)) {
            try {
                $this->connection = new \SQLite3($this->location);
                $this->runDBChecks();
            } catch (\Exception $e) {
                exit($e);
            }
        }
    }

    /**
     * Run database checks
     * @return void
     */
    private function runDBChecks(): void
    {
        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS "assets" (
            "id"    INTEGER NOT NULL UNIQUE COLLATE NOCASE,
            "location"  TEXT NOT NULL COLLATE NOCASE,
            "hash"  TEXT NOT NULL UNIQUE COLLATE NOCASE,
            PRIMARY KEY("id" AUTOINCREMENT)
        );
        SQL;

        $result = $this->queryDB($query, false);
    }

    /**
     * Query database
     * @param  string $query
     * @param  boolean $return_rows
     * @return \SQLite3Result|array<mixed>|boolean
     */
    private function queryDB(string $query, bool $return_rows = true): \SQLite3Result|array|bool
    {
        $statement = $this->connection->prepare($query);
        if ($statement instanceof \SQLite3Stmt) {
            $result = $statement->execute();
            $statement->close();
            return $return_rows && $result instanceof \SQLite3Result ? $result->fetchArray() : $result;
        }

        return false;
    }

    /**
     * Get asset records
     * @param  array<string>  $assets
     * @return array<mixed>
     */
    public function getAssetInfo(array $assets): array
    {
        return [];
    }
}
