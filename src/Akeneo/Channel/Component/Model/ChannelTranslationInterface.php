<?php

namespace Akeneo\Channel\Component\Model;

use Akeneo\Tool\Component\Localization\Model\TranslationInterface;

/**
 * Channel translation interface
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ChannelTranslationInterface extends TranslationInterface
{
    /**
     * Set label
     *
     * @param string $label
     */
    public function setLabel(string $label): \Akeneo\Channel\Component\Model\ChannelTranslationInterface;

    /**
     * Get the label
     */
    public function getLabel(): string;
}
