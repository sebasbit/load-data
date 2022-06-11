<?php

namespace Sebasbit\LoadData\Tests\Csv;

use PHPUnit\Framework\TestCase;
use Sebasbit\LoadData\Csv\CsvReader;
use Sebasbit\LoadData\Csv\CsvReaderException;

class CsvReaderTest extends TestCase
{
    public function test_create_with_path_to_file()
    {
        $file = __DIR__ . '/../Fixtures/test-no-headers.csv';
        $csvReader = new CsvReader($file);
        $this->assertEquals($file, $csvReader->origin());
        $this->assertNull($csvReader->headers());
        $this->assertNull($csvReader->currentRow());
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
        $file = __DIR__ . '/../Fixtures/test-no-headers.csv';
        $expected = ['1', 'Forrester Skittle', 'fskittle0@yale.edu'];
        $csvReader = new CsvReader($file);

        $this->assertEquals($expected, $csvReader->nextRow());
        $csvReader->reset();
        $this->assertEquals($expected, $csvReader->nextRow());
    }

    public function test_return_null_if_the_last_row_was_read()
    {
        $file = __DIR__ . '/../Fixtures/test-empty.csv';
        $csvReader = new CsvReader($file);
        $this->assertNull($csvReader->nextRow());
    }

    public function test_set_options_by_constructor()
    {
        $file = __DIR__ . '/../Fixtures/test-no-default-options.csv';
        $expected = ['1', 'Forrester; Skittle', 'fskittle0@yale.edu'];
        $csvReader = new CsvReader($file, ['length' => 41, 'delimiter' => ';', 'enclosure' => '#']);
        $this->assertEquals($expected, $csvReader->nextRow());
    }

    public function test_read_headers_if_option_is_set_to_true()
    {
        $file = __DIR__ . '/../Fixtures/test-with-headers.csv';
        $expected = ['id', 'name', 'email'];
        $csvReader = new CsvReader($file, ['headers' => true]);
        $this->assertEquals($expected, $csvReader->headers());
    }

    public function test_fail_if_headers_cannot_be_read()
    {
        $this->expectException(CsvReaderException::class);
        $this->expectExceptionMessage("The headers cannot be read");
        $file = __DIR__ . '/../Fixtures/test-empty.csv';
        $csvReader = new CsvReader($file, ['headers' => true]);
    }

    public function test_consider_headers_when_reading_rows()
    {
        $file = __DIR__ . '/../Fixtures/test-with-headers.csv';
        $expected = ['1', 'Forrester Skittle', 'fskittle0@yale.edu'];
        $csvReader = new CsvReader($file, ['headers' => true]);

        $this->assertEquals($expected, $csvReader->nextRow());
        $csvReader->reset();
        $this->assertEquals($expected, $csvReader->nextRow());
    }

    public function test_iterate_over_rows_using_a_foreach_loop()
    {
        $file = __DIR__ . '/../Fixtures/test-no-headers.csv';
        $expected = ['1', 'Forrester Skittle', 'fskittle0@yale.edu'];
        $csvReader = new CsvReader($file);

        foreach($csvReader as $rownum => $row) {
            $this->assertEquals(1, $rownum);
            $this->assertEquals($expected, $row);
        }
    }
}
