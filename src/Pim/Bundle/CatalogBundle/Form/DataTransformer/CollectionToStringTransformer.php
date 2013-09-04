<?php

namespace Pim\Bundle\CatalogBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * 
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CollectionToStringTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (!$value instanceof Collection) {
            throw new UnexpectedTypeException($value, 'Doctrine\\Common\\Collections\\Collection');
        }

        return implode(',', array_map(function ($elt) { 
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
    }
}

