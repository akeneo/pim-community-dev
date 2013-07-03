<?php

namespace Oro\Bundle\AddressBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\TranslationBundle\DataFixtures\AbstractTranslatableEntityFixture;

class LoadAddressTypeData extends AbstractTranslatableEntityFixture
{
    const ADDRESS_TYPE_PREFIX = 'address_type';

    /**
     * @var array
     */
    protected $addressTypes = array(
        AddressType::TYPE_BILLING,
        AddressType::TYPE_SHIPPING,
    );

    /**
     * @return array
     */
    protected function getAddressTypes()
    {
        return $this->addressTypes;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadEntities(ObjectManager $manager)
    {
        $addressTypeRepository = $manager->getRepository('OroAddressBundle:AddressType');

        $translationLocales = $this->getTranslationLocales();
        $addressTypes = $this->getAddressTypes();

        foreach ($translationLocales as $locale) {
            foreach ($addressTypes as $addressName) {
                // get address type entity
                /** @var AddressType $addressType */
                $addressType = $addressTypeRepository->findOneBy(array('name' => $addressName));
                if (!$addressType) {
                    $addressType = new AddressType($addressName);
                }

                // set locale and label
                $addressTypeLabel = $this->translate($addressName, static::ADDRESS_TYPE_PREFIX, $locale);
                $addressType->setLocale($locale)
                    ->setLabel($addressTypeLabel);

                // save
                $manager->persist($addressType);
            }

            $manager->flush();
        }
    }
}
