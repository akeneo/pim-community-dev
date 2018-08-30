<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;

/**
 * Product value for a reference data
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataValue extends AbstractValue implements ReferenceDataValueInterface
{
    /** @var ReferenceDataInterface */
    protected $data;

    /**
     * @param AttributeInterface          $attribute
     * @param string                      $channel
     * @param string                      $locale
     * @param ReferenceDataInterface|null $data
     */
    public function __construct(AttributeInterface $attribute, $channel, $locale, ReferenceDataInterface $data = null)
    {
        $this->setAttribute($attribute);
        $this->setScope($channel);
        $this->setLocale($locale);

        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return null !== $this->data ? (string) $this->data : '';
    }
}
