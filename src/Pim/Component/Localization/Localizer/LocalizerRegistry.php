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

    /**
     * {@inheritdoc}
     */
    public function getLocalizer($attributeType)
    {
        return $this->getSupporterLocalizer($this->localizers, $attributeType);
    }

    /**
     * {@inheritdoc}
     */
    public function addLocalizer(LocalizerInterface $localizer)
    {
        $this->localizers[] = $localizer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductValueLocalizer($attributeType)
    {
        return $this->getSupporterLocalizer($this->valueLocalizers, $attributeType);
    }

    /**
     * {@inheritdoc}
     */
    public function addProductValueLocalizer(LocalizerInterface $localizer)
    {
        $this->valueLocalizers[] = $localizer;

        return $this;
    }

    /**
     * Returns a LocalizerInterface supporting an attributeType.
     *
     * @param LocalizerInterface[] $localizers
     * @param string               $attributeType
     *
     * @return LocalizerInterface|null
     */
    protected function getSupporterLocalizer(array $localizers, $attributeType)
    {
        if (!empty($localizers)) {
            foreach ($localizers as $localizer) {
                if ($localizer->supports($attributeType)) {
                    return $localizer;
                }
            }
        }

        return null;
    }
}
