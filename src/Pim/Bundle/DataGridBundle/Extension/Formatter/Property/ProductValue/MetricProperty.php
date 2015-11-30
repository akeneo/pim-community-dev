<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue;

use Pim\Component\Localization\Presenter\PresenterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Metric field property, able to render metric attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricProperty extends TwigProperty
{
    /** @var PresenterInterface */
    protected $presenter;

    /**
     * @param \Twig_Environment   $environment
     * @param TranslatorInterface $translator
     * @param PresenterInterface  $presenter
     */
    public function __construct(
        \Twig_Environment $environment,
        TranslatorInterface $translator,
        PresenterInterface $presenter
    ) {
        parent::__construct($environment);

        $this->translator = $translator;
        $this->presenter  = $presenter;
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
            $formattedData = $this->presenter->present($data, ['locale' => $this->translator->getLocale()]);
            return $this->getTemplate()->render(
                [
                    'data' => $formattedData,
                    'unit' => $unit
                ]
            );
        }
    }
}
