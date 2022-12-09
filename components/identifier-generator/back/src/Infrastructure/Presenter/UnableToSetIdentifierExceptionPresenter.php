<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Presenter;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToSetIdentifierException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\Error;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UnableToSetIdentifierExceptionPresenter
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @return array<array{'path': string|null, "message": string}>
     */
    public function fromException(UnableToSetIdentifierException $exception): array
    {
        return \array_map(fn (Error $error): array => $this->fromError($error), $exception->getErrors()->toArray());
    }

    /**
     * @return array{'path': string|null, "message": string}
     */
    private function fromError(Error $error): array
    {
        return [
            'path' => $error->getPath(),
            'message' => $this->translator->trans($error->getMessage(), $error->getParameters()),
        ];
    }
}
