<?php

namespace Akeneo\Tool\Component\Localization\Presenter;

/**
 * Presenter Interface, to present readable data.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PresenterInterface
{
    /**
     * Presents a value to be readable
     *
     * @param mixed $value   The original value
     * @param array $options The options for presentation
     *
     * @return string
     */
    public function present($value, array $options = []);

    /**
     * Returns wether the presenter supports an attribute type.
     *
     * @param string $attributeType
     *
     * @return bool
     */
    public function supports($attributeType);
}
