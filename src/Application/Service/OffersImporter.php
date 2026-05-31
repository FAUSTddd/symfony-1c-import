<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Application\Service;

use FaustDDD\Symfony1cImport\Application\Command\ImportOffersCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class OffersImporter
{
    public function __invoke(ImportOffersCommand $command): void
    {
        $content = @file_get_contents($command->filePath);
        if ($content === false) {
            throw new \RuntimeException(sprintf('Cannot read file: %s', $command->filePath));
        }

        $xml = @simplexml_load_string($content);
        if (!$xml) {
            throw new \RuntimeException('Cannot parse XML');
        }

        if (!isset($xml->ПакетПредложений->Предложения->Предложение)) {
            return;
        }

        foreach ($xml->ПакетПредложений->Предложения->Предложение as $item) {
            $this->handleProduct($item);
        }
    }

    private function handleProduct(\SimpleXMLElement $item): void
    {
    }
}