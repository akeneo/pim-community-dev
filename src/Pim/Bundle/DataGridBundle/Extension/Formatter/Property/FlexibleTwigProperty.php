<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;

/**
 * Flexible field property that allow to configure a related template for value rendering
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleTwigProperty extends FieldProperty
{
    /**
     * @var string
     */
    const TEMPLATE_KEY = 'template';

    /** @var \Twig_Environment */
    protected $environment;

    /**
     * @param \Twig_Environment $environment
     */
    public function __construct(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        if ($value) {
            return $this->getTemplate()->render(
                array(
                    'value' => $value,
                )
            );
        }

        return null;
    }

    /**
     * Load twig template
     *
     * @return \Twig_TemplateInterface
     */
    protected function getTemplate()
    {
        return $this->environment->loadTemplate($this->get(self::TEMPLATE_KEY));
    }
}
