<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Application\Command;

final class ImportCatalogCommand
{
    public function __construct(public readonly string $filePath)
    {
    }
}