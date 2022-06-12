<?php

namespace Sebasbit\LoadData\Tests\Csv;

use PHPUnit\Framework\TestCase;
use Sebasbit\LoadData\Csv\CsvReader;
use Sebasbit\LoadData\Csv\CsvReaderException;

class CsvReaderTest extends TestCase
{
    protected array $fixtures;

    protected function setUp(): void
    {
        $this->fixtures = [
            'empty' => __DIR__ . '/../Fixtures/test-empty.csv',
            'no-default-options' => [
                __DIR__ . '/../Fixtures/test-no-default-options.csv',
                ['1', 'Forrester; Skittle', 'fskittle0@yale.edu']
            ],
            'no-headers' => [
                __DIR__ . '/../Fixtures/test-no-headers.csv',
                ['1', 'Forrester Skittle', 'fskittle0@yale.edu']
            ],
            'with-headers' => [
                __DIR__ . '/../Fixtures/test-with-headers.csv',
                ['id', 'name', 'email'],
                ['1', 'Forrester Skittle', 'fskittle0@yale.edu']
            ]
        ];
    }

    public function test_create_with_path_to_file()
    {
        $file = $this->fixtures['empty'];
        $csvReader = new CsvReader($file);
        $this->assertEquals($file, $csvReader->origin());
        $this->assertNull($csvReader->headers());
        $this->assertNull($csvReader->row());
    }

    public function test_fail_if_file_does_not_exists()
    {
        $this->expectException(CsvReaderException::class);
        $this->expectExceptionMessage("The file 'fail.csv' does not exists");

        $csvReader = new CsvReader('fail.csv');
    }

    public function test_fail_if_file_cannot_be_opened()
    {
        $this->expectException(CsvReaderException::class);
        $this->expectExceptionMessage("The file '.' cannot be opened");

        $csvReader = new CsvReader('.');
    }

    public function test_read_rows_from_the_file()
    {
        [$file, $expected] = $this->fixtures['no-headers'];
        $csvReader = new CsvReader($file);

        $this->assertEquals($expected, $csvReader->nextRow());
        $csvReader->reset();
        $this->assertEquals($expected, $csvReader->nextRow());
    }

    public function test_return_null_if_the_last_row_was_read()
    {
        $file = $this->fixtures['empty'];
        $csvReader = new CsvReader($file);
        $this->assertNull($csvReader->nextRow());
    }

    public function test_set_options_by_constructor()
    {
        [$file, $expected] = $this->fixtures['no-default-options'];
        $csvReader = new CsvReader($file, ['length' => 41, 'delimiter' => ';', 'enclosure' => '#']);
        $this->assertEquals($expected, $csvReader->nextRow());
    }

    public function test_consider_headers_when_reading_rows()
    {
        [$file, $headers, $expected] = $this->fixtures['with-headers'];
        $csvReader = new CsvReader($file, ['headers' => true]);

        $this->assertEquals($headers, $csvReader->headers());
        $this->assertEquals($expected, $csvReader->nextRow());
        $csvReader->reset();
        $this->assertEquals($expected, $csvReader->nextRow());
    }

    public function test_fail_if_headers_cannot_be_read()
    {
        $this->expectException(CsvReaderException::class);
        $this->expectExceptionMessage("The headers cannot be read");

        $file = $this->fixtures['empty'];
        $csvReader = new CsvReader($file, ['headers' => true]);
    }

    public function test_iterate_over_rows_using_a_foreach_loop()
    {
        [$file, $expected] = $this->fixtures['no-headers'];
        $csvReader = new CsvReader($file);

        foreach($csvReader as $rownum => $row) {
            $this->assertEquals(1, $rownum);
            $this->assertEquals($expected, $row);
        }
    }
}
