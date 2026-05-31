<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Application\Service;

use FaustDDD\Symfony1cImport\Application\Command\ImportCatalogCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CatalogImporter
{
    public function __invoke(ImportCatalogCommand $command): void
    {
        try {
            XmlStreamHelper::walkPath(
                $command->filePath,
                ['Товары', 'Товар'],
                [$this, 'handleProduct']
            );
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                sprintf('Import failed for file: %s', $command->filePath),
                0,
                $e
            );
        }
    }

    public function handleProduct(\SimpleXMLElement $item): void
    {
        // Базовая реализация — пустая, для переопределения в наследниках
    }
}