<?php

namespace Pim\Bundle\FlexibleEntityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Collection item
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class CollectionItemAbstract extends AbstractType
{
    /**
     * Returns choices array form type select box
     *
     * @return mixed
     */
    abstract public function getTypesArray();

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(
                [
                    'data_class'    => 'Pim\Bundle\FlexibleEntityBundle\Entity\Collection',
                    'required'      => false
                ]
            );
    }
}
