<?php

namespace Pim\Component\Localization\Localizer;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizerRegistry implements LocalizerRegistryInterface
{
    /** @var LocalizerInterface[] */
    protected $localizers = [];

    /** @var LocalizerInterface[] */
    protected $valueLocalizers = [];

    /** @var LocalizerInterface[] */
    protected $optionLocalizers = [];

    /**
     * {@inheritdoc}
     */
    public function getLocalizer($attributeType)
    {
        return $this->getSupportedLocalizer($this->localizers, $attributeType);
    }

    /**
     * {@inheritdoc}
     */
    public function registerLocalizer(LocalizerInterface $localizer)
    {
        $this->localizers[] = $localizer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductValueLocalizer($attributeType)
    {
        return $this->getSupportedLocalizer($this->valueLocalizers, $attributeType);
    }

    /**
     * {@inheritdoc}
     */
    public function registerProductValueLocalizer(LocalizerInterface $localizer)
    {
        $this->valueLocalizers[] = $localizer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeOptionLocalizer($optionName)
    {
        return $this->getSupportedLocalizer($this->optionLocalizers, $optionName);
    }

    /**
     * {@inheritdoc}
     */
    public function registerAttributeOptionLocalizer(LocalizerInterface $localizer)
    {
        $this->optionLocalizers[] = $localizer;

        return $this;
    }

    /**
     * Returns a LocalizerInterface supporting a value.
     *
     * @param LocalizerInterface[] $localizers
     * @param string               $value
     *
     * @return LocalizerInterface|null
     */
    protected function getSupportedLocalizer(array $localizers, $value)
    {
        if (!empty($localizers)) {
            foreach ($localizers as $localizer) {
                if ($localizer->supports($value)) {
                    return $localizer;
                }
            }
        }

        return null;
    }
}
