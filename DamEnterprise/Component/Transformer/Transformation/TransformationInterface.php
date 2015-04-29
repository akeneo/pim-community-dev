<?php

namespace DamEnterprise\Component\Transformer\Transformation;

interface TransformationInterface
{
    /**
     * @param \SplFileInfo $file
     * @param array        $options
     *
     * @return mixed
     */
    public function transform(\SplFileInfo $file, array $options = []);
    public function getName();
    public function supportsMimeType($mimeType);
    public function getMimeTypes();
}
