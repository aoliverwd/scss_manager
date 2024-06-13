<?php

namespace SCSSWrapper\Model;

class Compiler
{
    private const TABLE_NAME = 'assets';

    private string $location = '';
    private \SQLite3 $connection;

    /**
     * Constructor
     * @param array<mixed> $options
     */
    public function __construct(array $options = [])
    {
        $this->location = isset($options['db_location']) ? $options['db_location'] : __DIR__ . '/assets.db';
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
     * Return table name
     * @return string
     */
    private function getTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Run database checks
     * @return void
     */
    private function runDBChecks(): void
    {
        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS {$this->getTableName()} (
            "id"    INTEGER NOT NULL UNIQUE COLLATE NOCASE,
            "filename"  TEXT NOT NULL COLLATE NOCASE,
            "location_id"  TEXT NOT NULL UNIQUE COLLATE NOCASE,
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
        $return_assets = [];

        foreach ($assets as $asset) {
            if (file_exists($asset)) {
                $location_hash = hash('crc32b', $asset);
                $file_hash = hash_file('sha256', $asset);
                $filename = base64_encode(basename($asset));

                $return_assets[] = [
                    'location' => $asset,
                    'filename' => $filename,
                    'location_id' => $location_hash,
                    'hash' => $file_hash,
                    'updated' => false
                ];

                $result = $this->connection->querySingle(
                    <<<SQL
                    select * from {$this->getTableName()}
                    where location_id = "{$location_hash}";
                    SQL,
                    true
                );

                // Add to database
                if (empty($result)) {
                    $return_assets[array_key_last($return_assets)]['updated'] = true;

                    $statement = $this->connection->prepare(
                        <<<SQL
                        insert into {$this->getTableName()}
                        (filename, location_id, hash)
                        values ("{$filename}", "{$location_hash}", "{$file_hash}");
                        SQL
                    );

                    if ($statement instanceof \SQLite3Stmt) {
                        $add_result = $statement->execute();
                        $statement->close();

                        // Error adding record
                        if (!$add_result) {
                            array_pop($return_assets);
                        }
                    } else {
                        array_pop($return_assets);
                    }
                } elseif (isset($result['id']) && isset($result['hash']) && $file_hash !== $result['hash']) {
                    $return_assets[array_key_last($return_assets)]['updated'] = true;

                    // Update record
                    $statement = $this->connection->prepare(
                        <<<SQL
                        update {$this->getTableName()}
                        set hash = "{$file_hash}"
                        where id = {$result['id']};
                        SQL
                    );

                    if ($statement instanceof \SQLite3Stmt) {
                        $statement->execute();
                        $statement->close();
                    }
                }
            }
        }

        return $return_assets;
    }
}
