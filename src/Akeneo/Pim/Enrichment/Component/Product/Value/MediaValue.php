<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
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
     * {@inheritdoc}
     */
    protected function __construct(string $attributeCode, ?FileInfoInterface $data, ?string $scopeCode, ?string $localeCode)
    {
        parent::__construct($attributeCode, $data, $scopeCode, $localeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): ?FileInfoInterface
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return null !== $this->data ? $this->data->getKey() : '';
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual(ValueInterface $value): bool
    {
        if (!$value instanceof MediaValueInterface ||
            $this->getScopeCode() !== $value->getScopeCode() ||
            $this->getLocaleCode() !== $value->getLocaleCode()) {
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
