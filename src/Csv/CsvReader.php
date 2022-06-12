<?php declare(strict_types=1);

namespace Sebasbit\LoadData\Csv;

use Iterator;

use function array_merge;
use function fclose;
use function fgetcsv;
use function file_exists;
use function fopen;
use function rewind;

/**
 * The CsvReader class offers an easy way to read data from csv files.
 */
class CsvReader implements Iterator
{
    const READ_MODE = 'r';

    /**
     * File path.
     *
     * @var string
     */
    private string $origin;

    /**
     * Default configuration options.
     *
     * @var array
     */
    private array $defaultOptions = [
        'headers' => false,
        'length' => 0,
        'delimiter' => ',',
        'enclosure' => '"',
        'escape' => '\\',
    ];

    /**
     * Configuration options.
     *
     * @var array
     */
    private array $options;

    /**
     * Pointer to csv file.
     *
     * @var resource
     */
    private $pointer;

    /**
     * Columns headers.
     *
     * @var array|null
     */
    private ?array $headers;

    /**
     * Current row data.
     *
     * @var array|null
     */
    private ?array $row;

    /**
     * Current row number.
     *
     * @var int
     */
    private int $rownum;

    /**
     * Indicate if the last row was read.
     *
     * @var bool
     */
    private bool $endOfFile;

    /**
     * Create a new CsvReader for a csv file, it will use the options
     * to read each line.
     *
     * Options: headers, length, delimiter, enclosure, escape
     *
     * @param string $origin
     * @param array $options
     * @throws \Sebasbit\LoadData\Csv\CsvReaderException
     */
    public function __construct(string $origin, array $options = [])
    {
        if (!file_exists($origin)) {
            throw new CsvReaderException("The file '$origin' does not exists");
        }

        $pointer = @fopen($origin, self::READ_MODE);

        if (!$pointer) {
            throw new CsvReaderException("The file '$origin' cannot be opened");
        }

        $this->origin = $origin;
        $this->pointer = $pointer;
        $this->options = array_merge($this->defaultOptions, $options);

        // Set default values
        $this->reset();
    }

    public function __destruct()
    {
        if ($this->pointer) {
            fclose($this->pointer);
        }
    }

    /**
     * Get file path.
     *
     * @return string
     */
    public function origin(): string
    {
        return $this->origin;
    }

    /**
     * Get current row number.
     *
     * @return int
     */
    public function rownum(): int
    {
        return $this->rownum;
    }

    /**
     * Return true if the last row was read, false otherwise.
     *
     * @return bool
     */
    public function endOfFile(): bool
    {
        return $this->endOfFile;
    }

    /**
     * Get columns headers.
     *
     * @return array|null
     */
    public function headers(): ?array
    {
        return $this->headers;
    }

    /**
     * Get current row data.
     *
     * @return array|null
     */
    public function row(): ?array
    {
        return $this->row;
    }

    /**
     * Move pointer to beginning of file, set default options and read
     * headers if option is set to true.
     *
     * @return void
     * @throws \Sebasbit\LoadData\Csv\CsvReaderException
     */
    public function reset(): void
    {
        rewind($this->pointer);

        $this->headers = null;
        $this->row = null;
        $this->rownum = 0;
        $this->endOfFile = false;

        if ($this->options['headers'] === true) {
            $headers = $this->nextRow();

            if (!$headers) {
                throw new CsvReaderException('The headers cannot be read');
            }

            $this->headers = $headers;
        }
    }

    /**
     * Move pointer to next row and return data. If there are not rows
     * to read, it return null.
     *
     * @return array|null
     */
    public function nextRow(): ?array
    {
        $data = fgetcsv(
            $this->pointer,
            $this->options['length'],
            $this->options['delimiter'],
            $this->options['enclosure'],
            $this->options['escape']
        );

        if (!$data) {
            $this->endOfFile = true;
            return null;
        }

        $this->rownum++;
        $this->row = $data;

        return $data;
    }

    // Implementation of Iterator class.

    public function current()
    {
        return $this->row();
    }

    public function key()
    {
        return $this->rownum();
    }

    public function next(): void
    {
        $this->nextRow();
    }

    public function rewind(): void
    {
        $this->reset();
        $this->nextRow(); // Set row data for first iteration
    }

    public function valid(): bool
    {
        return $this->endOfFile() === false;
    }
}
