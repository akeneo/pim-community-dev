<?php

namespace Pim\Component\Catalog\Localization\Localizer;

use Akeneo\Component\Localization\Localizer\LocalizerInterface;

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
    public function register(LocalizerInterface $localizer)
    {
        $this->localizers[] = $localizer;

        return $this;
    }

    /**
     * Get a localizer supported by value
     *
     * @param string $value
     *
     * @return LocalizerInterface|null
     */
    public function getLocalizer($value)
    {
        foreach ($this->localizers as $localizer) {
            if ($localizer->supports($value)) {
                return $localizer;
            }
        }

        return null;
    }
}
