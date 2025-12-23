<?php
// src/Application/Service/XmlStreamHelper.php
namespace FaustDDD\Symfony1cImport\Application\Service;

final class XmlStreamHelper
{
    /**
     * Потоковый обход всех элементов $tagName в файле.
     * Для каждого элемента вызывается $callback(SimpleXMLElement $element)
     */
    public static function walk(string $filePath, string $tagName, callable $callback): int
    {
        $reader = new \XMLReader();
        if (!$reader::open($filePath)) {
            throw new \RuntimeException("Cannot open XML file: $filePath");
        }

        $count = 0;
        while ($reader->read()) {
            if ($reader->nodeType === \XMLReader::ELEMENT && $reader->localName === $tagName) {
                $element = new \SimpleXMLElement($reader->readOuterXML());
                $callback($element);
                $count++;
                $reader->next(); // перескок к следующему элементу с тем же именем
            }
        }
        $reader->close();
        return $count; // сколько элементов обработали
    }

    /**
     * Потоковый обход элементов по вложенному пути.
     * Пример: ['Товары', 'Товар'] → найдёт все <Товар> внутри <Товары>.
     * Возвращает количество найденных элементов.
     */
    public static function walkPath(string $filePath, array $path, callable $callback): int
    {
        $reader = new \XMLReader();
        if (!$reader::open($filePath)) {
            throw new \RuntimeException("Cannot open XML file: $filePath");
        }

        $depth   = 0;
        $target  = end($path);          // последний элемент пути
        $count   = 0;

        while ($reader->read()) {
            // зашли в нужный родительский элемент
            if ($reader->nodeType === \XMLReader::ELEMENT && $reader->localName === $path[$depth]) {
                $depth++;
                if ($depth === count($path)) {
                    // нашли конечный элемент
                    $element = new \SimpleXMLElement($reader->readOuterXML());
                    $callback($element);
                    $count++;
                    $depth--; // вышли из конечного элемента
                    $reader->next(); // перескок к следующему элементу с тем же именем
                }
            } elseif ($reader->nodeType === \XMLReader::END_ELEMENT && $reader->localName === $path[$depth - 1]) {
                $depth--;
            }
        }

        $reader->close();
        return $count;
    }
}