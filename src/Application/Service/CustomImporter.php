<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Application\Service;

use FaustDDD\Symfony1cImport\Application\Command\ImportCatalogCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CustomImporter
{
    public function __construct()
    {
    }

    public function __invoke(ImportCatalogCommand $command): void
    {
        $content = @file_get_contents($command->filePath);
        if ($content === false) {
            throw new \RuntimeException(sprintf('Cannot read file: %s', $command->filePath));
        }

        $xml = @simplexml_load_string($content);
        if (!$xml) {
            throw new \RuntimeException('Cannot parse XML');
        }

        foreach ($xml->Каталог->Товары->Товар as $item) {
            $this->handleProduct($item);
        }
    }

    private function handleProduct(\SimpleXMLElement $item): void
    {
    }
}