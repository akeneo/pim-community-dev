<?php

namespace Oro\Bundle\NavigationBundle\Annotation;

/**
 * Title service annotation parser
 * @package Oro\Bundle\NavigationBundle\Annotation
 * @Annotation
 * @Target({"METHOD"})
 */
class TitleTemplate
{
    /**
     * @var string
     */
    private $titleTemplate;

    /**
     * @param  array             $data
     * @throws \RuntimeException
     */
    public function __construct(array $data)
    {
        $titleTemplate = isset($data['value']) ? $data['value'] : false;

        if ($titleTemplate === false) {
            throw new \RuntimeException('Template annotation should contain "template" part');
        }

        $this->titleTemplate = $titleTemplate;
    }

    /**
     * Returns annotation data
     *
     * @return string
     */
    public function getTitleTemplate()
    {
        return $this->titleTemplate;
    }
}
