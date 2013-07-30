<?php

namespace Oro\Bundle\TranslationBundle\Form\DataTransformer;

use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer as DoctrineCollectionToArrayTransformer;

class CollectionToArrayTransformer extends DoctrineCollectionToArrayTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform($collection)
    {
        // process any empty value (string, array)
        if (empty($collection)) {
            return array();
        }

        return parent::transform($collection);
    }
}
