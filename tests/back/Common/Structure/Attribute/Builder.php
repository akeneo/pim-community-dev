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

    public function __construct()
    {
        $this->code = Code::fromString('code');
        $this->type = new Type(AttributeTypes::IDENTIFIER);
    }

    /**
     * @return Model\Attribute
     */
    public function build(): Model\Attribute
    {
        $attribute = new Model\Attribute();
        $attribute->setCode((string) $this->code);
        $attribute->setType((string) $this->type);

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

    /**
     * @return Builder
     */
    public function aIdentifier(): Builder
    {
        $this->type = new Type(AttributeTypes::IDENTIFIER);

        return $this;
    }
}
