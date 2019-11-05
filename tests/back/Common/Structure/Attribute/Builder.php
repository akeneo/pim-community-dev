<?php

declare(strict_types=1);

namespace Akeneo\Test\Common\Structure\Attribute;

use Akeneo\Test\Common\EntityWithValue\Code;
use Akeneo\Pim\Structure\Component\Model;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @Todo This builder should be improved. For now, you can use to create a identifier attribute with a code
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Builder
{
    /** @var string */
    private $code;

    /** @var string */
    private $type;

    /** @var bool */
    private $isUnique;

    /** @var bool */
    private $localizable;

    /** @var bool */
    private $scopable;

    /** @var string */
    private $backendType;

    public function __construct()
    {
        $this->code = Code::fromString('code');
        $this->type = new Type(AttributeTypes::IDENTIFIER);
        $this->isUnique = false;
        $this->localizable = false;
        $this->scopable = false;
        $this->backendType = AttributeTypes::BACKEND_TYPE_TEXT;
    }

    /**
     * @return Model\Attribute
     */
    public function build(): Model\Attribute
    {
        $attribute = new Model\Attribute();
        $attribute->setCode((string) $this->code);
        $attribute->setType((string) $this->type);
        $attribute->setUnique($this->isUnique);
        $attribute->setScopable($this->scopable);
        $attribute->setLocalizable($this->localizable);
        $attribute->setDecimalsAllowed(false);
        $attribute->setBackendType($this->backendType);

        return $attribute;
    }

    /**
     * @param string $code
     *
     * @return Builder
     */
    public function withCode(string $code): Builder
    {
        $this->code = $code;

        return $this;
    }

    public function aTextAttribute(): Builder
    {
        $this->type = new Type(AttributeTypes::TEXT);
        $this->backendType = AttributeTypes::BACKEND_TYPE_TEXT;

        return $this;
    }

    public function aPriceCollectionAttribute(): Builder
    {
        $this->type = new Type(AttributeTypes::PRICE_COLLECTION);
        $this->backendType = AttributeTypes::BACKEND_TYPE_PRICE;

        return $this;
    }

    public function aUniqueAttribute(): Builder
    {
        $this->type = new Type(AttributeTypes::TEXT);
        $this->isUnique = true;
        $this->localizable = false;
        $this->scopable = false;
        $this->isUnique = true;
        $this->backendType = AttributeTypes::BACKEND_TYPE_TEXT;

        return $this;
    }

    /**
     * @return Builder
     */
    public function aIdentifier(): Builder
    {
        $this->type = new Type(AttributeTypes::IDENTIFIER);
        $this->localizable = false;
        $this->scopable = false;
        $this->isUnique = true;
        $this->backendType = AttributeTypes::BACKEND_TYPE_TEXT;

        return $this;
    }

    public function localizable(): Builder
    {
        $this->localizable = true;

        return $this;
    }

    public function scopable(): Builder
    {
        $this->scopable = true;

        return $this;
    }
}
