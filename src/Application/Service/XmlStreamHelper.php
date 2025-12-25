<?php
// src/Application/Service/XmlStreamHelper.php
namespace FaustDDD\Symfony1cImport\Application\Service;

use RuntimeException;
use Throwable;

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
            throw new RuntimeException("Cannot open XML file: $filePath");
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
     *
     * @throws RuntimeException если файл не удалось открыть
     * @throws Throwable        может быть выброшено из $callback
     */
    public static function walkPath(string $filePath, array $path, callable $callback): int
    {
        $reader = new \XMLReader();
        if (!$reader->open($filePath)) {
            throw new RuntimeException("Cannot open XML file: $filePath");
        }

        $depth = 0;
        $count = 0;
        $top   = $path[0] ?? null;          // имя корневого узла, который нас интересует

        while ($reader->read()) {
            /* -------- открытие элемента -------- */
            if ($reader->nodeType === \XMLReader::ELEMENT) {
                if ($depth < count($path) && $reader->localName === $path[$depth]) {
                    $depth++;
                    if ($depth === count($path)) {        // дошли до нужного узла
                        $element = new \SimpleXMLElement($reader->readOuterXML());
                        $callback($element);
                        $count++;
                        $depth--;                         // возвращаемся на уровень родителя
                        if (!$reader->next()) {           // переход к следующему сиблингу
                            break;
                        }
                        continue;
                    }
                }
            }

            /* -------- закрытие элемента -------- */
            if ($reader->nodeType === \XMLReader::END_ELEMENT
                && $reader->localName === $top          // закрывается первый элемент пути
                && $depth === 1) {                      // мы как раз покидаем его
                break;                                  // больше ничего не интересует
            }

            if ($depth > 0 && $reader->nodeType === \XMLReader::END_ELEMENT) {
                $depth--;
            }
        }

        $reader->close();
        return $count;
    }
}