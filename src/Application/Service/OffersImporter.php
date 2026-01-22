<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Application\Service;

use FaustDDD\Symfony1cImport\Application\Command\ImportOffersCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Filesystem\Filesystem;

#[AsMessageHandler]
class OffersImporter
{

    public function __invoke(ImportOffersCommand $command): void
    {
        $xml = simplexml_load_string(file_get_contents($command->filePath));
        if (!$xml) {
            throw new \RuntimeException('Cannot parse XML');
        }

        // пример: обрабатываем только товары
        foreach ($xml->ПакетПредложений->Предложения->Предложение as $item) {
            $this->handleProduct($item);
        }

    }

    private function handleProduct(\SimpleXMLElement $item): void
    {

    }
}