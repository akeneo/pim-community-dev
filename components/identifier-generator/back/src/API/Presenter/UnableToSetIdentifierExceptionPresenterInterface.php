<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\API\Presenter;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToSetIdentifierException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UnableToSetIdentifierExceptionPresenterInterface
{
    /**
     * @return array<array{'path': string|null, "message": string}>
     */
    public function present(UnableToSetIdentifierException $exception): array;
}
