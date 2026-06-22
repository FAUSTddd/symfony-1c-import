# Symfony 1С Битрикс Import
[![Tests](https://github.com/FAUSTddd/symfony-1c-import/actions/workflows/tests.yml/badge.svg)](https://github.com/FAUSTddd/symfony-1c-import/actions/workflows/tests.yml)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)
![Symfony](https://img.shields.io/badge/Symfony-6.4%20|%207.x%20|%208.0-green)

Библиотека для интеграции Symfony с 1С:Предприятие по протоколу CommerceML.

## Требования

| PHP | Symfony |
|-----|---------|
| 8.2+ | 6.4, 7.x |
| 8.4+ | 8.x |

## Установка
```bash
composer require faustddd/symfony-1c-import
```

## Подключение
```bash
// config/bundles.php
return [
    // ...
    FaustDDD\Symfony1cImport\Symfony1cImportBundle::class => ['all' => true],
];
```

## Конфигурация
```bash
# config/packages/faustddd_1c_import.yaml
faustddd_1c_import:
    endpoint: '/1c/exchange'
    login: '%env(IMPORT_1C_LOGIN)%'
    password: '%env(IMPORT_1C_PASSWORD)%'
   
# .env
IMPORT_1C_LOGIN=admin
IMPORT_1C_PASSWORD=change_me
```
## Использование
| Вариант                | Файл 1С      | Что нужно                     | Когда             |
| ---------------------- | ------------ | ----------------------------- | ----------------- |
| **Импорт каталога**    | `import.xml` | Наследовать `CatalogImporter` | Товары, категории |
| **Импорт предложений** | `offers.xml` | Наследовать `OffersImporter`  | Цены, остатки     |
| **Кастомный импорт**   | Любой XML    | Наследовать `CustomImporter`  | Свой формат       |

## Пример использования
```bash
#[AsMessageHandler]
class MyCatalogImporter extends CatalogImporter
{
    protected function handleProduct(\SimpleXMLElement $item): void
    {
        // Ваша логика
    }
}
```
## Протокол CommerceML
1С делает 4 запроса: checkauth → init → file → import

## Лицензия
MIT
