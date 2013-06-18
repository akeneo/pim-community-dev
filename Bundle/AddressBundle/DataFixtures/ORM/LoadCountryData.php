<?php

namespace Oro\Bundle\AddressBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;

class LoadCountryData extends AbstractFixture implements OrderedFixtureInterface
{
    const DEFAULT_LOCALE = 'en';

    /**
     * Load address types
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $fileName = $this->getFileName();
        $countries = Yaml::parse(realpath($fileName));
        $this->saveCountryData($manager, $countries);
    }

    /**
     * Save countries and regions in DB
     *
     * @param ObjectManager $manager
     * @param array $countries
     */
    protected function saveCountryData(ObjectManager $manager, array $countries)
    {
        foreach ($countries as $countryData) {
            if (empty($countryData['name']) || empty($countryData['iso2']) || empty($countryData['iso3'])) {
                continue;
            }

            $country = new Country($countryData['name'], $countryData['iso2'], $countryData['iso3']);
            if (!empty($countryData['units'])) {
                foreach ($countryData['units'] as $regionName) {
                    $region = new Region();
                    // TODO: extract region codes from external DB and add to fixture
                    $regionCode = strtoupper(substr($regionName, 0, 8));
                    $region->setName($regionName)
                        ->setCode($regionCode)
                        ->setCountry($country);
                    $country->addRegion($region);
                }
            }
            $manager->persist($country);
        }

        $manager->flush();
    }

    /**
     * Get list of localized countries and regions
     *
     * @return string
     * @throws \LogicException
     */
    protected function getFileName()
    {
        $locale = \Locale::getDefault();
        if ($locale) {
            $locale = substr($locale, 0, 2);
            if ($this->isLocaleFileExists($locale)) {
                return $this->getLocaleFileName($locale);
            }
        }

        $locale = self::DEFAULT_LOCALE;
        if ($this->isLocaleFileExists($locale)) {
            return $this->getLocaleFileName($locale);
        }

        // if there is no default file
        throw new \LogicException('There is no translation country file for locale "' . self::DEFAULT_LOCALE. '".');
    }

    /**
     * @param string $locale
     * @return string
     */
    protected function getLocaleFileName($locale)
    {
        return __DIR__ . '/../translations/countries.' . $locale . '.yml';
    }

    /**
     * @param string $locale
     * @return bool
     */
    protected function isLocaleFileExists($locale)
    {
        $fileName = $this->getLocaleFileName($locale);
        return is_file($fileName) && is_readable($fileName);
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 10;
    }
}
