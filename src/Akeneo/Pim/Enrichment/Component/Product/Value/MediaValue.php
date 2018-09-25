<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

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
    /** @var FileInfoInterface|null */
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

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $value)
    {
        if (!$value instanceof MediaValueInterface ||
            $this->getScope() !== $value->getScope() ||
            $this->getLocale() !== $value->getLocale()) {
            return false;
        }

        $comparedMedia = $value->getData();
        $thisMedia = $this->getData();

        if (null === $thisMedia && null === $comparedMedia) {
            return true;
        }
        if (null === $thisMedia || null === $comparedMedia) {
            return false;
        }

        return $comparedMedia->getOriginalFilename() === $thisMedia->getOriginalFilename() &&
            $comparedMedia->getMimeType() === $thisMedia->getMimeType() &&
            $comparedMedia->getSize() === $thisMedia->getSize() &&
            $comparedMedia->getExtension() === $thisMedia->getExtension() &&
            $comparedMedia->getHash() === $thisMedia->getHash() &&
            $comparedMedia->getKey() === $thisMedia->getKey() &&
            $comparedMedia->getStorage() === $thisMedia->getStorage();
    }
}
