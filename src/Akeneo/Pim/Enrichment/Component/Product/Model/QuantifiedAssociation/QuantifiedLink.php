<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedLink
{
    private const QUANTITY_KEY = 'quantity';
    private const IDENTIFIER_KEY = 'identifier';

    /** @var string */
    private $identifier;

    /** @var int */
    private $quantity;

    public function __construct(string $identifier, int $quantity)
    {
        Assert::stringNotEmpty($identifier, 'Quantified link identifier cannot be empty');

        $this->identifier = $identifier;
        $this->quantity = $quantity;
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function normalize(): array
    {
        return [
            self::IDENTIFIER_KEY => $this->identifier,
            self::QUANTITY_KEY => $this->quantity
        ];
    }
}
