<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\Entity;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class IdentifierMapping
{
    private $id;
    private $pimAiCode;
    private $attribute;

    public function __construct(?int $id, string $pimAiCode, AttributeInterface $attribute)
    {
        $this->id = $id;
        $this->pimAiCode = $pimAiCode;
        $this->attribute = $attribute;
    }

    /**
     * @return mixed
     */
    public function getPimAiCode(): string
    {
        return $this->pimAiCode;
    }

    /**
     * @return mixed
     */
    public function getAttribute(): AttributeInterface
    {
        return $this->attribute;
    }

    /**
     * @param mixed $attribute
     *
     * @return IdentifierMapping
     */
    public function updateAttribute($attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }


}
