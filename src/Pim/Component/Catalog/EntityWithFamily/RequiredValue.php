<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\EntityWithFamily;

use Pim\Component\Catalog\Model\AbstractValue;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;

/**
 * A required value is the translation of the attribute requirements
 * {@see Pim\Component\Catalog\Model\AttributeRequirementInterface} in terms of values.
 *
 * Therefore a required value contains no data. It simply expresses the fact that for instance:
 *  - a "sku" is required on all channels and all locales
 *  - a description is required on the channel "ecommerce" and the locale "en_US"
 *  - a name is required on all channels and the locale "en_US"
 *  - a price is required on the channel "ecommerce" and all locales
 *  - ...
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RequiredValue extends AbstractValue implements ValueInterface
{
    /**
     * @param AttributeInterface $attribute
     * @param string             $channel
     * @param string             $locale
     */
    public function __construct(AttributeInterface $attribute, string $channel = null, string $locale = null)
    {
        $this->setAttribute($attribute);
        $this->setScope($channel);
        $this->setLocale($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return null;
    }
}
