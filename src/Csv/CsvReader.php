<?php declare(strict_types=1);

namespace Sebasbit\LoadData\Csv;

use Iterator;

use function array_merge;
use function fclose;
use function fgetcsv;
use function file_exists;
use function fopen;
use function rewind;

class CsvReader implements Iterator
{
    const READ_MODE = 'r';

    private string $origin;

    private array $defaultOptions = [
        'headers' => false,
        'length' => 0,
        'delimiter' => ',',
        'enclosure' => '"',
        'escape' => '\\',
    ];

    private array $options;

    /**
     * @var resource
     */
    private $pointer;

    private ?array $headers;

    private ?array $row;

    private int $rownum;

    private bool $endOfFile;

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

    public function origin(): string
    {
        return $this->origin;
    }

    public function rownum(): int
    {
        return $this->rownum;
    }

    public function endOfFile(): bool
    {
        return $this->endOfFile;
    }

    public function headers(): ?array
    {
        return $this->headers;
    }

    public function row(): ?array
    {
        return $this->row;
    }

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

    // Iterator methods implementation

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

        // Set default value for the first iteration
        $this->nextRow();
    }

    public function valid(): bool
    {
        return $this->endOfFile() === false;
    }
}
