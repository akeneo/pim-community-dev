<?php

namespace Akeneo\Tool\Component\Localization;

use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;

/**
 * Translate updater
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslatableUpdater
{
    /**
     * Update data to translate label
     *
     * @param TranslatableInterface $object
     * @param array                 $data
     */
    public function update(TranslatableInterface $object, array $data)
    {
        foreach ($data as $localeCode => $label) {
            $object->setLocale($localeCode);
            $translation = $object->getTranslation();

            if (null === $label || '' === $label) {
                $object->removeTranslation($translation);
            } else {
                $translation->setLabel($label);
            }
        }
    }
}
