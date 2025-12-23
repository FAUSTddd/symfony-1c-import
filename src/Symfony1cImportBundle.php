<?php
// src/Symfony1cImportBundle.php
namespace FaustDDD\Symfony1cImport;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use FaustDDD\Symfony1cImport\DependencyInjection\Symfony1cImportExtension;

final class Symfony1cImportBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new Symfony1cImportExtension();
        }

        return $this->extension;
    }
}