<?php

namespace App\Tools;

use Doctrine\DBAL\FetchMode;
use PDO;

class NewPDO extends CustomPDO
{

    public function __construct()
    {
        $parsedDBURL = parse_url($_ENV["DATABASE_URL"]);
        $host = $parsedDBURL["host"] . ":" . $parsedDBURL["port"];
        parent::construct($host, $parsedDBURL["user"], $parsedDBURL["pass"], substr($parsedDBURL["path"], 1));
    }

}
