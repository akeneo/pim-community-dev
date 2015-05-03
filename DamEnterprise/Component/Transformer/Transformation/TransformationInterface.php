<?php

namespace DamEnterprise\Component\Transformer\Transformation;

use DamEnterprise\Component\Transformer\Options\TransformationOptionsResolverInterface;

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

    /**
     * @return TransformationOptionsResolverInterface
     */
    public function getOptionsResolver();

    /**
     * @param TransformationOptionsResolverInterface $optionsResolver
     *
     * @return TransformationInterface
     */
    public function setOptionsResolver($optionsResolver);
}
