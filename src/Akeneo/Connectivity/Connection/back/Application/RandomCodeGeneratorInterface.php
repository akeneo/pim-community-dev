<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application;

interface RandomCodeGeneratorInterface
{
    public function generate(): string;
}
