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

    /**
     * {@inheritdoc}
     */
    public function getLocalizer($attributeType)
    {
        if (!empty($this->localizers)) {
            foreach ($this->localizers as $localizer) {
                if ($localizer->supports($attributeType)) {
                    return $localizer;
                }
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function addLocalizer(LocalizerInterface $localizer)
    {
        $this->localizers[] = $localizer;

        return $this;
    }
}
