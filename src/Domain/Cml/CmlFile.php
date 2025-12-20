<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Domain\Cml;

final class CmlFile
{
    public function __construct(public readonly string $fullPath)
    {
    }

    public function name(): string
    {
        return basename($this->fullPath);
    }

    public function content(): string
    {
        return file_get_contents($this->fullPath);
    }
}