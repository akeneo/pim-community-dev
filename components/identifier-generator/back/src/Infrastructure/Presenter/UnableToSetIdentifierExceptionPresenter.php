<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Presenter;

use Akeneo\Pim\Automation\IdentifierGenerator\API\Presenter\UnableToSetIdentifierExceptionPresenterInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToSetIdentifierException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\Error;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UnableToSetIdentifierExceptionPresenter implements UnableToSetIdentifierExceptionPresenterInterface
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @return array<array{'path': string|null, "message": string}>
     */
    public function present(UnableToSetIdentifierException $exception): array
    {
        return \array_map(fn (Error $error): array => $this->presentError($error), $exception->getErrors()->toArray());
    }

    /**
     * @return array{'path': string|null, "message": string}
     */
    private function presentError(Error $error): array
    {
        return [
            'path' => $error->getPath(),
            'message' => $this->translator->trans($error->getMessage(), $error->getParameters()),
        ];
    }
}
