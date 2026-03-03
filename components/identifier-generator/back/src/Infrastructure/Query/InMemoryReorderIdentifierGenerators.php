<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Query;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query\ReorderIdentifierGenerators;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository\InMemoryIdentifierGeneratorRepository;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryReorderIdentifierGenerators implements ReorderIdentifierGenerators
{
    public function __construct(private readonly IdentifierGeneratorRepository $repository)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function byCodes(array $codes): void
    {
        Assert::isInstanceOf($this->repository, InMemoryIdentifierGeneratorRepository::class);
        $this->repository->reorder(\array_map(
            static fn (IdentifierGeneratorCode $code): string => $code->asString(),
            $codes
        ));
    }
}
