<?php

declare(strict_types=1);

namespace Oro\Bundle\PimFilterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This is an hack to accept multiple types in a form type, such as string and array.
 * Actually, since this PR https://github.com/symfony/symfony/pull/29307, array is not accepted anymore
 * when using native TextType.
 *
 * It prevents us to use filters in the datagrid accepting multiple type of values:
 * - a list of string for IN LIST operator
 * - a string for IS NOT EMPTY operator
 *
 * It is a BC break in a minor release because we were using a bug as a feature.
 *
 * @see https://github.com/symfony/symfony/issues/29809
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnstructuredType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'compound' => false,
            'multiple' => true,
        ));
    }
}
