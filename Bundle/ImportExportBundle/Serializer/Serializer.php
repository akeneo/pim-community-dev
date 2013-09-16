<?php

namespace Oro\Bundle\ImportExportBundle\Serializer;

use Symfony\Component\Serializer\Serializer as BaseSerializer;
use Doctrine\Common\Collections\Collection;

class Serializer extends BaseSerializer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($data, $format = null, array $context = array())
    {
        if ($data instanceof Collection) {
            // Clear cache of normalizer for collections,
            // because of wrong behaviour when selecting normalizer for collections of elements with different types
            unset($this->normalizerCache[get_class($data)][$format]);
        }
        return parent::normalize($data, $format, $context);
    }
}
