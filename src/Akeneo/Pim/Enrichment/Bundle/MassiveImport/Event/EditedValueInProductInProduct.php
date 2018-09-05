<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Event;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

class EditedValueInProduct
{
    /** @var string */
    private $identifier;

    /** @var ValueInterface */
    private $value;

    /**
     * @param string         $identifier
     * @param ValueInterface $value
     */
    public function __construct($identifier, ValueInterface $value)
    {
        $this->identifier = $identifier;
        $this->value = $value;
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function value(): ValueInterface
    {
        return $this->value;
    }
}
