<?php declare(strict_types=1);

namespace Sebasbit\LoadData\Db;

interface Statement
{
    public function sentence(): string;
    public function parameters(): array;
}
