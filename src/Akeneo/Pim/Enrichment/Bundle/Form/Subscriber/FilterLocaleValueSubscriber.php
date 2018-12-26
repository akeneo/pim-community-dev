<?php

namespace Akeneo\Pim\Enrichment\Bundle\Form\Subscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Filter locale value subscriber
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterLocaleValueSubscriber implements EventSubscriberInterface
{
    /** @var string $currentLocale */
    protected $currentLocale;

    /** @var string $comparisonLocale */
    protected $comparisonLocale;

    /**
     * @param string $currentLocale
     * @param string $comparisonLocale
     */
    public function __construct($currentLocale, $comparisonLocale)
    {
        $this->currentLocale = $currentLocale;
        $this->comparisonLocale = $comparisonLocale;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        foreach ($data as $name => $value) {
            if ($this->currentLocale
                && $value->isLocalizable()
                && !$this->isInCurrentLocale($value)
                && !$this->isInComparisonLocale($value)
            ) {
                $form->remove($name);
            }

            if ($this->isInComparisonLocale($value)) {
                $form->add(
                    $name,
                    'pim_product_value',
                    [
                        'disabled'     => true,
                        'block_config' => [
                            'mode' => 'comparison'
                        ]
                    ]
                );
            }
        }
    }

    /**
     * @param ValueInterface $value
     *
     * @return bool
     */
    protected function isInCurrentLocale(ValueInterface $value)
    {
        return $value->isLocalizable() && $value->getLocaleCode() === $this->currentLocale;
    }

    /**
     * @param ValueInterface $value
     *
     * @return bool
     */
    protected function isInComparisonLocale(ValueInterface $value)
    {
        return $value->isLocalizable() && $value->getLocaleCode() === $this->comparisonLocale;
    }
}
