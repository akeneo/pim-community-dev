<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue;

use Pim\Component\Localization\Formatter\FormatterInterface;

/**
 * Metric field property, able to render metric attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricProperty extends TwigProperty
{
    /** @var FormatterInterface */
    protected $formatter;

    /**
     * @param \Twig_Environment  $environment
     * @param FormatterInterface $formatter
     */
    public function __construct(\Twig_Environment $environment, FormatterInterface $formatter)
    {
        parent::__construct($environment);

        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        $result = $this->getBackendData($value);
        $data   = isset($result['data']) ? $result['data'] : null;
        $unit   = $result['unit'];

        if ($data && $unit) {
            $formattedData = $this->formatter->format($data);
            return $this->getTemplate()->render(
                array(
                    'data' => $formattedData,
                    'unit' => $unit
                )
            );
        }
    }
}
