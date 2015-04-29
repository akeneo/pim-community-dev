<?php

namespace DamEnterprise\Component\Transformer;

use DamEnterprise\Component\Transformer\Transformation\TransformationRegistry;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

class Transformer
{
    /** @var TransformationRegistry */
    protected $registry;

    public function __construct(TransformationRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function transform(\SplFileInfo $file, array $rawTransformations)
    {
        $mimeType = MimeTypeGuesser::getInstance()->guess($file->getPathname());

        foreach ($rawTransformations as $name => $options) {
            $transformation = $this->registry->get($name, $mimeType);
            $transformation->transform($file, $options);
        }

        return $this;
    }

}
