<?php

namespace Pim\Component\Catalog\Value;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Pim\Component\Catalog\Model\AbstractValue;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Product value for attribute types:
 *   - pim_catalog_image
 *   - pim_catalog_file
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaValue extends AbstractValue implements MediaValueInterface
{
    /** @var FileInfoInterface */
    protected $data;

    /**
     * @param AttributeInterface     $attribute
     * @param string                 $channel
     * @param string                 $locale
     * @param FileInfoInterface|null $data
     */
    public function __construct(AttributeInterface $attribute, $channel, $locale, FileInfoInterface $data = null)
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
        return null !== $this->data ? $this->data->getKey() : '';
    }
}
