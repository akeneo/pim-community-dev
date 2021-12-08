<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

interface RandomCodeGeneratorInterface
{
    public function generate(): string;
}
