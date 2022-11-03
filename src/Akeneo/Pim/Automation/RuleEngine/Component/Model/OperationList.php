<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class OperationList implements \IteratorAggregate
{
    /** @var Operation[] */
    private $operations = [];

    private function __construct(array $operations)
    {
        $this->operations = $operations;
    }

    public static function fromNormalized(array $data): self
    {
        Assert::notEmpty($data, 'The operation list expects at least one operation');
        return new self(
            array_map(function (array $operations): Operation {
                return Operation::fromNormalized($operations);
            }, $data)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->operations);
    }
}
