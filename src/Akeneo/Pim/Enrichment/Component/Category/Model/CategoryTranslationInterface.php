<?php

namespace Akeneo\Pim\Enrichment\Component\Category\Model;

use Akeneo\Tool\Component\Localization\Model\TranslationInterface;

/**
 * Category translation interface
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CategoryTranslationInterface extends TranslationInterface
{
    /**
     * Set label
     *
     * @param string $label
     *
     * @return CategoryTranslationInterface
     */
    public function setLabel($label);

    /**
     * Get the label
     *
     * @return string
     */
    public function getLabel();
}
