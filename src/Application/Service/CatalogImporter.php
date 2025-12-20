<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Application\Service;

use FaustDDD\Symfony1cImport\Application\Command\ImportCatalogCommand;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Filesystem\Filesystem;

#[AsMessageHandler]
final class CatalogImporter
{
    public function __construct(
        private EntityManagerInterface $em,
        private ProductRepository $productRepository,
        private Filesystem $fs
    ) {
    }

    public function __invoke(ImportCatalogCommand $command): void
    {
        $xml = simplexml_load_string(file_get_contents($command->filePath));
        if (!$xml) {
            throw new \RuntimeException('Cannot parse XML');
        }

        // пример: обрабатываем только товары
        foreach ($xml->Каталог->Товары->Товар as $item) {
            $this->handleProduct($item);
        }

        $this->em->flush();
    }

    private function handleProduct(\SimpleXMLElement $item): void
    {
        $ean = (string) $item->Артикул;
        if (!$ean) {
            return;
        }

        $product = $this->productRepository->findOneBy(['ean' => $ean])
            ?? new \App\Entity\Product();

        $product->setName((string) $item->Наименование);
        $product->setEan($ean);
        $product->setEnabled(true);

        $this->em->persist($product);
    }
}