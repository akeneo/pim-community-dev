<?php

namespace Oro\Bundle\FilterBundle\Twig;

use Twig\Extension\AbstractExtension as TwigExtension;

abstract class AbstractExtension extends TwigExtension
{
    /**
     * Extension name
     */
    const NAME = 'oro_filter_abstract';

    /**
     * @var string
     */
    protected $templateName;

    /**
     * @var array
     */
    protected $defaultFunctionOptions = [
        'is_safe'           => ['html'],
        'needs_environment' => true
    ];

    /**
     * @param string $templateName
     */
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
    }
}
