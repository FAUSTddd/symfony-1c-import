# Import-слой: 1С ↔ CommerceML ↔ Symfony 7

## Назначение
Отвечает за весь жизненный цикл обмена данными с 1С по протоколу CommerceML:
- приём файлов `import.xml` / `offers.xml`
- парсинг и сохранение каталога, остатков, цен
- фоновая или синхронная обработка

## Структура папок (clean architecture)

src/Import/  
│  
├─ Application/               ← сценарии (use-cases)  
│  ├─ Command/  
│  │   └─ ImportCatalogCommand.php    → DTO «импортируй этот XML»  
│  │  
│  └─ Service/  
│      ├─ Cml1cInteractor.php         → «разговаривает» с 1С (checkauth/init/file/import)  
│      └─ CatalogImporter.php         → парсит XML и пишет в БД (handler команды)  
│  
├─ Domain/                    ← чистая предметная область (без зависимостей)  
│  └─ Cml/  
│      ├─ CmlFile.php         → value-object «файл CommerceML»  
│      └─ CmlMode.php         → enum режимов 1С (checkauth/init/file/import)  
│  
└─ Infrastructure/            → адаптеры «наружу»  
└─ Controller/  
└─ Import1CController.php   → маршрут /1c/exchange, делегирует Cml1cInteractor

## Логика работы слоёв

1. 1С делает HTTP-запрос  
   `GET /1c/exchange?type=catalog&mode=checkauth`

2. **Infrastructure / Controller**  
   `Import1CController` создаёт `Request` и вызывает **сервис приложения** `Cml1cInteractor`

3. **Application / Service / Cml1cInteractor**
    - превращает `?mode=` в `enum CmlMode`
    - для `checkauth/init` сразу возвращает текст-ответ
    - для `file` сохраняет тело запроса в `var/import/`
    - для `import` создаёт `ImportCatalogCommand` и **кидает её в шину** (`MessageBus`)

4. **MessageBus** (Symfony Messenger)
    - может быть синхронным (обрабатывается мгновенно)
    - или асинхронным (Redis/RabbitMQ) — 1С сразу получит `success`, а импорт пойдёт в фоне

5. **Application / Service / CatalogImporter** (handler команды)
    - читает XML через `simplexml`
    - маппит узлы в сущности (`Product`, `Category`, `Manufacturer` …)
    - сохраняет через `EntityManager`

6. **Domain / Cml / …**
    - `CmlMode` — native PHP-enum
    - `CmlFile` — value-object без зависимостей  
      → легко тестировать unit-тестами, не требует Symfony

## Команды для быстрого старта

```bash
# очередь обрабатывается синхронно (по-умолчанию)
php bin/console messenger:consume async

# если переделали под async (Redis/Rabbit)
php bin/console messenger:consume async --time-limit=3600