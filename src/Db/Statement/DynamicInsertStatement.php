<?php declare(strict_types=1);

namespace Sebasbit\LoadData\Db\Statement;

use Sebasbit\LoadData\Db\Statement;

class DynamicInsertStatement implements Statement
{
    private string $tableName;
    private array $values;

    public function __construct(string $tableName, array $values)
    {
        $this->tableName = $tableName;
        $this->values = $values;
    }

    public function sentence(): string
    {
        return sprintf(
            'INSERT INTO `%s` VALUES (%s)',
            $this->tableName,
            trim(str_repeat('?,', count($this->values)), ',')
        );
    }

    public function parameters(): array
    {
        return $this->values;
    }
}
