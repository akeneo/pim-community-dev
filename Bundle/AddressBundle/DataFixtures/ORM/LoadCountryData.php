<?php

namespace Oro\Bundle\AddressBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Finder\Finder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;

class LoadCountryData extends AbstractFixture implements ContainerAwareInterface
{
    const COUNTRY_DOMAIN      = 'countries';
    const COUNTRY_FILE_REGEXP = '/^countries\.(.*?)\./';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $translationDirectory = '/Resources/translations/';

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->translator = $this->container->get('translator');

        $fileName = $this->getFileName();
        $countries = $this->getDataFromFile($fileName);
        $this->saveCountryData($manager, $countries);
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    protected function getFileName()
    {
        return __DIR__ . '/../data/countries.yml';
    }

    /**
     * @param string $fileName
     * @return bool
     */
    protected function isFileAvailable($fileName)
    {
        return is_file($fileName) && is_readable($fileName);
    }

    /**
     * @param string $fileName
     * @return array
     * @throws \LogicException
     */
    protected function getDataFromFile($fileName)
    {
        if (!$this->isFileAvailable($fileName)) {
            throw new \LogicException('File ' . $fileName . 'is not available');
        }

        $fileName = realpath($fileName);

        return Yaml::parse($fileName);
    }

    /**
     * @return array
     */
    protected function getAvailableCountryLocales()
    {
        $translationDirectory = str_replace('/', DIRECTORY_SEPARATOR, $this->translationDirectory);
        $translationDirectories = array();

        foreach ($this->container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            $bundleTranslationDirectory = dirname($reflection->getFilename()) . $translationDirectory;
            if (is_dir($bundleTranslationDirectory) && is_readable($bundleTranslationDirectory)) {
                $translationDirectories[] = realpath($bundleTranslationDirectory);
            }
        }

        $finder = new Finder();
        $finder->in($translationDirectories)->name(self::COUNTRY_FILE_REGEXP);

        $countryLocales = array();
        /** @var $file \SplFileInfo */
        foreach ($finder as $file) {
            preg_match(self::COUNTRY_FILE_REGEXP, $file->getFilename(), $matches);
            if ($matches) {
                $countryLocales[] = $matches[1];
            }
        }

        return $countryLocales;
    }

    /**
     * @param string $locale
     * @param array $countryData
     * @return null|Country
     */
    protected function createCountry($locale, array $countryData)
    {
        if (empty($countryData['iso2code']) || empty($countryData['iso3code'])) {
            return null;
        }

        $country = new Country($countryData['iso2code']);
        $countryName = $this->translator->trans(
            $countryData['iso2code'],
            array(),
            self::COUNTRY_DOMAIN,
            $locale
        );
        $country->setIso3Code($countryData['iso3code'])
            ->setName($countryName);

        return $country;
    }

    /**
     * @param $locale
     * @param Country $country
     * @param array $regionData
     * @return null|Region
     */
    protected function createRegion($locale, Country $country, array $regionData)
    {
        if (empty($regionData['combinedCode']) || empty($regionData['code'])) {
            return null;
        }

        $region = new Region($regionData['combinedCode']);
        $regionName = $this->translator->trans(
            $regionData['combinedCode'],
            array(),
            self::COUNTRY_DOMAIN,
            $locale
        );
        $region->setCode($regionData['code'])
            ->setName($regionName);

        $region->setCountry($country);

        return $region;
    }

    /**
     * Save countries and regions to DB
     *
     * @param ObjectManager $manager
     * @param array $countries
     */
    protected function saveCountryData(ObjectManager $manager, array $countries)
    {
        foreach ($this->getAvailableCountryLocales() as $locale) {
            foreach ($countries as $countryData) {
                $country = $this->createCountry($locale, $countryData);
                if (!$country) {
                    continue;
                }
                $manager->persist($country);

                if (!empty($countryData['regions'])) {
                    foreach ($countryData['regions'] as $regionData) {
                        $region = $this->createRegion($locale, $country, $regionData);
                        if ($region) {
                            $manager->persist($region);
                        }
                    }
                }

                $manager->flush();
            }
        }
    }
}
