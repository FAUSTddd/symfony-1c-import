<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Tests\Unit\Domain\Cml;

use FaustDDD\Symfony1cImport\Domain\Cml\CmlFile;
use PHPUnit\Framework\TestCase;

class CmlFileTest extends TestCase
{
    private string $tempFile;

    protected function setUp(): void
    {
        $this->tempFile = sys_get_temp_dir() . '/test_cml_' . uniqid() . '.xml';
        file_put_contents($this->tempFile, '<?xml version="1.0"?><Тест/>');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function testNameReturnsBaseName(): void
    {
        $file = new CmlFile($this->tempFile);

        self::assertSame(basename($this->tempFile), $file->name());
    }

    public function testContentReturnsFileContents(): void
    {
        $file = new CmlFile($this->tempFile);

        self::assertSame('<?xml version="1.0"?><Тест/>', $file->content());
    }

    public function testConstructorStoresFullPath(): void
    {
        $file = new CmlFile($this->tempFile);

        self::assertSame($this->tempFile, $file->fullPath);
    }
}