<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Domain\Cml;

enum CmlMode: string
{
    case CheckAuth = 'checkauth';
    case Init      = 'init';
    case File      = 'file';
    case Import    = 'import';
}