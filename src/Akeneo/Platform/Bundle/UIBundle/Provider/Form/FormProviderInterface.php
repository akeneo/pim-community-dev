<?php

namespace Akeneo\Platform\Bundle\UIBundle\Provider\Form;

/**
 * Form provider interface. The goal of this class is to provide the form name to render for
 * the given entity.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FormProviderInterface
{
    /**
     * Get the Form for the given element
     *
     * @param mixed $element
     *
     * @throws NoCompatibleFormProviderFoundException If no form is found for the given element
     *
     * @return string
     */
    public function getForm($element): string;

    /**
     * Does the Form provider support the element
     *
     * @param mixed $element
     *
     * @return bool
     */
    public function supports($element): bool;
}
