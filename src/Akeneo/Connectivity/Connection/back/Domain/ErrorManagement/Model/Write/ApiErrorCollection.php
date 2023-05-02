<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType;

class ApiErrorCollection
{
    /**
     * @var array<ErrorTypes::*, ApiErrorInterface[]>
     */
    private array $apiErrors;

    /**
     * @param ApiErrorInterface[] $apiErrors
     */
    public function __construct(array $apiErrors = [])
    {
        $this->apiErrors = \array_fill_keys(ErrorTypes::getAll(), []);
        foreach ($apiErrors as $apiError) {
            if (!$apiError instanceof ApiErrorInterface) {
                throw new \InvalidArgumentException(
                    \sprintf(
                        'Class "%s" accepts only "%s" in the collection.',
                        self::class,
                        ApiErrorInterface::class
                    )
                );
            }
            $this->add($apiError);
        }
    }

    public function add(ApiErrorInterface $error): void
    {
        /** @phpstan-var ErrorTypes::* $errorType */
        $errorType = (string) $error->type();

        $this->apiErrors[$errorType][] = $error;
    }

    public function count(?string $errorType = null): int
    {
        if (null === $errorType) {
            $count = 0;
            foreach ($this->apiErrors as $errors) {
                $count += \count($errors);
            }

            return $count;
        }
        $type = new ErrorType($errorType);

        return \count($this->apiErrors[(string) $type]);
    }

    /**
     * @return ApiErrorInterface[]
     */
    public function getByType(string $errorType): array
    {
        $type = new ErrorType($errorType);

        return $this->apiErrors[(string) $type];
    }

    /**
     * @return array<string, ApiErrorInterface[]>
     */
    public function getSorted(): array
    {
        return $this->apiErrors;
    }
}
