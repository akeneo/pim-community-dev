<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query\GetNextIdentifierQuery;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GenerateAutoNumberHandler implements GeneratePropertyHandlerInterface
{
    public function __construct(
        private GetNextIdentifierQuery $getNextIdentifierQuery
    ) {
    }

    public function __invoke(PropertyInterface $autoNumber, IdentifierGenerator $identifierGenerator, string $prefix): string
    {
        Assert::isInstanceOf($autoNumber, AutoNumber::class);
        $nextIdentifier = $this->getNextIdentifierQuery->fromPrefix($identifierGenerator, $prefix);

        if ($nextIdentifier < $autoNumber->numberMin()) {
            $nextIdentifier = $autoNumber->numberMin();
        }

        return str_pad('' . $nextIdentifier, $autoNumber->digitsMin(), '0', STR_PAD_LEFT);
    }

    public function getPropertyClass(): string
    {
        return AutoNumber::class;
    }
}
