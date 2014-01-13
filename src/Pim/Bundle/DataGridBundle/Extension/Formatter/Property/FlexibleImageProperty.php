<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;

/**
 * Flexible image field property, able to render image attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleImageProperty extends FieldProperty
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
        if ($value->getMedia() && $fileName = $value->getMedia()->getFileName()) {
            return $this->getTemplate()->render(
                array(
                    'value' => $fileName,
                    'title' => $value->getMedia()->getOriginalFilename()
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
