<?php declare(strict_types=1);

namespace Sebasbit\LoadData\Csv;

use Iterator;

class CsvReader implements Iterator
{
    private string $origin;

    /** @var resource */
    private $pointer;

    private array $options = [
        'headers' => false,
        'length' => 0,
        'delimiter' => ',',
        'enclosure' => '"',
        'escape' => '\\',
    ];

    private ?array $headers = null;

    private ?array $currentRow = null;

    private int $currentRownum = 0;

    private bool $endReached = false;

    public function __construct(string $origin, array $options = [])
    {
        if (!file_exists($origin)) {
            throw new CsvReaderException("The file '$origin' does not exists");
        }

        $pointer = @fopen($origin, 'r');

        if (!$pointer) {
            throw new CsvReaderException("The file '$origin' cannot be opened");
        }

        $this->origin = $origin;
        $this->pointer = $pointer;
        $this->options = array_merge($this->options, $options);

        if ($this->options['headers'] === true) {
            $headers = $this->nextRow();

            if (!$headers) {
                throw new CsvReaderException('The headers cannot be read');
            }

            $this->headers = $headers;
        }
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
            $this->endReached = true;
            return null;
        }

        $this->currentRownum++;
        $this->currentRow = $data;

        return $data;
    }

    public function reset(): void
    {
        rewind($this->pointer);

        $this->endReached = false;
        $this->headers = null;
        $this->currentRow = null;
        $this->currentRownum = 0;

        if ($this->options['headers'] === true) {
            $headers = $this->nextRow();

            if (!$headers) {
                throw new CsvReaderException('The headers cannot be read');
            }

            $this->headers = $headers;
        }
    }

    public function currentRownum(): int
    {
        return $this->currentRownum;
    }

    public function endReached(): bool
    {
        return $this->endReached;
    }

    public function headers(): ?array
    {
        return $this->headers;
    }

    public function currentRow(): ?array
    {
        return $this->currentRow;
    }

    // Iterator implementation
    public function current()
    {
        return $this->currentRow();
    }

    public function key()
    {
        return $this->currentRownum();
    }

    public function next(): void
    {
        $this->nextRow();
    }

    public function rewind(): void
    {
        $this->reset();
        $this->nextRow(); // set default value for the first iteration
    }

    public function valid(): bool
    {
        return ($this->endReached() === false);
    }
}
