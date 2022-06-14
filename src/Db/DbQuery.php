<?php declare(strict_types=1);

namespace Sebasbit\LoadData\Db;

use PDO;
use PDOException;

class DbQuery
{
    private PDO $pdo;

    public function __construct(string $host, string $database, string $user, string $password)
    {
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        } catch (PDOException $e) {
            throw new DbQueryException('Unable to open a database connection', $e->getCode());
        }
    }

    public function execute(Statement $statement): int
    {
        $sentence = $statement->sentence();
        $parameters = $statement->parameters();

        $stmt = $this->pdo->prepare($sentence);
        $stmt->execute($parameters);

        return $stmt->rowCount();
    }
}
