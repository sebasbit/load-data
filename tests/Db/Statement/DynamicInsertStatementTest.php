<?php

namespace Sebasbit\LoadData\Tests\Db\Statement;

use PHPUnit\Framework\TestCase;
use Sebasbit\LoadData\Db\Statement\DynamicInsertStatement;

class DynamicInsertStatementTest extends TestCase
{
    public function test_return_statement_with_table_name_and_mark_placeholders()
    {
        $expected = 'INSERT INTO `table_name` VALUES (?,?,?)';

        $statement = new DynamicInsertStatement('table_name', [1, 2, 3]);

        $this->assertEquals($expected, $statement->sentence());
    }

    public function test_return_same_values_received_by_constructor()
    {
        $statement = new DynamicInsertStatement('table_name', [1, 2, 3]);

        $this->assertEquals([1, 2, 3], $statement->parameters());
    }
}
