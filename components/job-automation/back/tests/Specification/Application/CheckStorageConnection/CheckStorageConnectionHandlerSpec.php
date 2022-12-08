<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\JobAutomation\Application\CheckStorageConnection;

use Akeneo\Platform\JobAutomation\Application\CheckStorageConnection\CheckStorageConnectionHandler;
use Akeneo\Platform\JobAutomation\Application\CheckStorageConnection\CheckStorageConnectionQuery;
use Akeneo\Platform\JobAutomation\Domain\Model\Storage\AmazonS3Storage;
use PhpSpec\ObjectBehavior;

class CheckStorageConnectionHandlerSpec extends ObjectBehavior
{
    public function it_is_instantiable(): void
    {
        $this->beAnInstanceOf(CheckStorageConnectionHandler::class);
    }
}
