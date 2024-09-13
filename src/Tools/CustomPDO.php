<?php


namespace App\Tools;

use PDO;


class CustomPDO
{

    /**
     * @var PDO
     */
    public $connection;

    public function construct($host, $username, $password, $dbname = null)
    {
        $dsn = "mysql:host=" . $host . ";";
        if (!empty($dbname)) {
            $dsn .= "dbname=" . $dbname . ";charset=UTF8";
        }
        $this->connection = new PDO($dsn, $username, $password);

        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param $query
     * @param array $params
     * @return array
     */
    public function fetch($query, array $params = []): array
    {
        if (!is_array($params)) $params = [$params];
        $prepare = $this->connection->prepare($query);
        $prepare->execute($params);
        $prepare->setFetchMode(PDO::FETCH_ASSOC);
        $result = $prepare->fetchAll();
        $prepare->closeCursor();

        return $result;
    }

    public function exec($query, $params = null)
    {
        if (!is_array($params)) $params = [$params];
        $prepare = $this->connection->prepare($query);
        $prepare->execute($params);
        $prepare->closeCursor();
    }

    /**
     * Returns the last inserted ID.
     * @param $query
     * @param array $params
     */
    public function insert($query, $params = [])
    {
        $stmt = $this->connection->prepare($query);
        $stmt->execute(is_array($params) ? $params : [$params]);
        $li = +$this->connection->lastInsertId();
        $stmt->closeCursor();
        return $li;
    }


    public function pQMS($qmsCount): string
    {
        return "(" . join(",", array_fill(0, $qmsCount, "?")) . ")";
    }

    public function genNupletsQMS($qmsPerNuplet, $nupletsCount)
    {
        return join(",", array_fill(0, $nupletsCount, $this->pQMS($qmsPerNuplet)));
    }

    public function aPQMS($arr)
    {
        return $this->pQMS(sizeof($arr));
    }

    public function aNPQMS($arr, $qmsPerNuplet)
    {
        return $this->genNupletsQMS($qmsPerNuplet, sizeof($arr) / $qmsPerNuplet);
    }

    public function insertInto(string $table, array $valueName, array $value)
    {
        $this->exec("
            INSERT INTO $table (
                " . join(', ', $valueName) . "
            ) VALUES " . $this->aNPQMS($value, count($valueName)) . "
        ", $value);
    }

    public function insertIgnoreInto(string $table, array $valueName, array $value)
    {
        $this->exec("
            INSERT IGNORE INTO $table (
                " . join(', ', $valueName) . "
            ) VALUES " . $this->aNPQMS($value, count($valueName)) . "
        ", $value);
    }

    public function getFirstAvailableId($table_name)
    {
        $row = $this->fetch("SELECT max(id) AS max_id FROM " . $table_name);
        if (empty($row[0]["max_id"])) return 1;
        return +$row[0]["max_id"] + 1;
    }

    public function fetchIntegerIds($query, $params = null)
    {
        $results = $this->fetch($query, $params);
        if (empty($results)) return [];
        $ids = [];
        foreach ($results as $res) {
            $ids[] = +$res["id"];
        }
        return $ids;
    }

    public function fetchIds($query, $params = null)
    {
        $results = $this->fetch($query, $params);
        if (empty($results)) return [];
        $ids = [];
        foreach ($results as $res) {
            $ids[] = $res["id"];
        }
        return $ids;
    }

    /**
     * @param $baseQuery string the query up to 'VALUES' (ex : 'INSERT INTO bla_bla(id,attr1,attr2) VALUES' )
     * @param $params [] the flat php array containing all the parameters.
     * @param $perInsertQMCount integer the amount of attributes filled in the insert (3 for the previous example)
     * @param $maxImportPerBatch integer the maximum amount of rows to insert in a single call.
     *
     */
    public function largeInsert($baseQuery, $params, $perInsertQMCount, $maxImportPerBatch = 5000)
    {
        $currentOffset = 0;
        $sub = array_slice($params, $currentOffset, $maxImportPerBatch * $perInsertQMCount);
        while (sizeof($sub)) {
            $this->exec($baseQuery . $this->genNupletsQMS($perInsertQMCount, count($sub) / $perInsertQMCount), $sub);
            $currentOffset = $currentOffset + $maxImportPerBatch * $perInsertQMCount;
            $sub = array_slice($params, $currentOffset, $maxImportPerBatch * $perInsertQMCount);
        }
    }


    /**
     * Returns an array of the extracted property.
     * @param $propertyName
     * @param $array
     * @param bool $purgeNullValues
     * @return array
     * @throws \Exception
     */
    public function extractProperty($propertyName, $array, $purgeNullValues = false): array
    {
        if (is_array($array)) {
            $result = [];
            foreach ($array as $item) {
                if (!$purgeNullValues || $item[$propertyName] != null) {
                    $result[] = $item[$propertyName];
                }
            }
            return $result;
        } else {
            throw new \Exception("Provided object is not a valid array");
        }
    }


    public function indexArray($array, $index)
    {
        $result = [];
        foreach ($array as $item) {
            $result[$item[$index]] = $item;
        }
        return $result;
    }

}
