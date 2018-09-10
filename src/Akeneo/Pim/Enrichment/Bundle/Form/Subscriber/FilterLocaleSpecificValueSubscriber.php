<?php

namespace Akeneo\Pim\Enrichment\Bundle\Form\Subscriber;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Filter locale specific value subscriber to remove value available in only a set of locales
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterLocaleSpecificValueSubscriber implements EventSubscriberInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var string $currentLocale */
    protected $currentLocale;

    public function __construct(?string $currentLocale, IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->currentLocale = $currentLocale;
        $this->attributeRepository = $attributeRepository;
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

        if (null === $data || null === $this->currentLocale) {
            return;
        }

        foreach ($data as $name => $value) {
            $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());

            if ($attribute->isLocaleSpecific()) {
                $availableCodes = $attribute->getLocaleSpecificCodes();
                if (!in_array($this->currentLocale, $availableCodes)) {
                    $form->remove($name);
                }
            }
        }
    }
}
