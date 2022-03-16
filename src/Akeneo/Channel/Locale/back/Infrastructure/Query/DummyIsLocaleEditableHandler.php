<?php

declare(strict_types=1);

namespace Akeneo\Channel\Locale\Infrastructure\Query;

use Akeneo\Channel\Locale\API\Query\IsLocaleEditableQuery;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DummyIsLocaleEditableHandler implements MessageHandlerInterface
{
    public function __invoke(IsLocaleEditableQuery $isLocaleEditableQuery): bool
    {
        // The query always return true in CE
        return true;
    }
}
