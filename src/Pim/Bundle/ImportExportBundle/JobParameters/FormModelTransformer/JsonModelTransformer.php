<?php

namespace Pim\Bundle\ImportExportBundle\JobParameters\FormModelTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class JsonModelTransformer implements DataTransformerInterface
{
    /**
     * Transforms an associative array to a string (json).
     *
     * @param array $object
     *
     * @return string
     */
    public function transform($object)
    {
        return json_encode($object);
    }

    /**
     * Transforms a string (json) to an associative array
     *
     * @param  string $field value
     *
     * @return array
     */
    public function reverseTransform($field)
    {
        return json_decode($field, true);
    }
}
