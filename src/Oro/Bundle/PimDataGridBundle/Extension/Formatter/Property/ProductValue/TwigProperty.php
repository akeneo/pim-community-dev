<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property\ProductValue;

use Twig\Environment;
use Twig\Template;

/**
 * Allows to configure a related template for value rendering
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TwigProperty extends FieldProperty
{
    /** @staticvar string */
    const TEMPLATE_KEY = 'template';

    protected $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        if ($value) {
            return $this->getTemplate()->render(['value' => $value]);
        }

        return null;
    }

    protected function getTemplate(): Template
    {
        return $this->environment->loadTemplate($this->get(self::TEMPLATE_KEY));
    }
}
