<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Tests\Unit\Domain\Cml;

use FaustDDD\Symfony1cImport\Domain\Cml\CmlMode;
use PHPUnit\Framework\TestCase;

class CmlModeTest extends TestCase
{
    public function testFromStringReturnsCorrectEnum(): void
    {
        self::assertSame(CmlMode::CheckAuth, CmlMode::from('checkauth'));
        self::assertSame(CmlMode::Init, CmlMode::from('init'));
        self::assertSame(CmlMode::File, CmlMode::from('file'));
        self::assertSame(CmlMode::Import, CmlMode::from('import'));
    }

    public function testFromInvalidStringThrowsException(): void
    {
        $this->expectException(\ValueError::class);
        CmlMode::from('invalid');
    }

    public function testCasesHaveCorrectValues(): void
    {
        self::assertSame('checkauth', CmlMode::CheckAuth->value);
        self::assertSame('init', CmlMode::Init->value);
        self::assertSame('file', CmlMode::File->value);
        self::assertSame('import', CmlMode::Import->value);
    }
}