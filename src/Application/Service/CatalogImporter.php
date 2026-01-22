<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Application\Service;

use FaustDDD\Symfony1cImport\Application\Command\ImportCatalogCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Filesystem\Filesystem;

#[AsMessageHandler]
class CatalogImporter
{

    public function __invoke(ImportCatalogCommand $command): void
    {
        XmlStreamHelper::walkPath($command->filePath, ['Товары','Товар'], [$this, 'handleProduct']);

    }

    private function handleProduct(\SimpleXMLElement $item): void
    {

    }
}