<?php

namespace Oro\Bundle\AddressBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Finder\Finder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

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
     * @var EntityRepository
     */
    protected $countryRepository;

    /**
     * @var EntityRepository
     */
    protected $regionRepository;

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
        $this->updateTranslations($manager);
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
    protected function getCountry($locale, array $countryData)
    {
        if (empty($countryData['iso2Code']) || empty($countryData['iso3Code'])) {
            return null;
        }

        /** @var $country Country */
        $country = $this->countryRepository->findOneBy(array('iso2Code' => $countryData['iso2Code']));
        if (!$country) {
            $country = new Country($countryData['iso2Code']);
            $country->setIso3Code($countryData['iso3Code']);
        }

        if ($locale) {
            $countryName = $this->translator->trans(
                $countryData['iso2Code'],
                array(),
                self::COUNTRY_DOMAIN,
                $locale
            );
        } else {
            $countryName = $countryData['iso2Code'];
        }

        $country->setLocale($locale)
            ->setName($countryName);

        return $country;
    }

    /**
     * @param string $locale
     * @param Country $country
     * @param array $regionData
     * @return null|Region
     */
    protected function getRegion($locale, Country $country, array $regionData)
    {
        if (empty($regionData['combinedCode']) || empty($regionData['code'])) {
            return null;
        }

        /** @var $region Region */
        $region = $this->regionRepository->findOneBy(array('combinedCode' => $regionData['combinedCode']));
        if (!$region) {
            $region = new Region($regionData['combinedCode']);
            $region->setCode($regionData['code'])
                ->setCountry($country);
        }

        if ($locale) {
            $regionName = $this->translator->trans(
                $regionData['combinedCode'],
                array(),
                self::COUNTRY_DOMAIN,
                $locale
            );
        } else {
            $regionName = $regionData['combinedCode'];
        }

        $region->setLocale($locale)
            ->setName($regionName);

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
        $this->countryRepository = $manager->getRepository('OroAddressBundle:Country');
        $this->regionRepository  = $manager->getRepository('OroAddressBundle:Region');

        $countryLocales = $this->getAvailableCountryLocales();
        // null element performs entity initialization and save of basic not translatable entity
        array_unshift($countryLocales, null);

        foreach ($countryLocales as $locale) {
            foreach ($countries as $countryData) {
                $country = $this->getCountry($locale, $countryData);
                if (!$country) {
                    continue;
                }

                $manager->persist($country);

                if (!empty($countryData['regions'])) {
                    foreach ($countryData['regions'] as $regionData) {
                        $region = $this->getRegion($locale, $country, $regionData);
                        if (!$region) {
                            continue;
                        }

                        $manager->persist($region);
                    }
                }
            }

            $manager->flush();
            $manager->clear();
        }
    }

    /**
     * Update foreign keys in translation tables
     *
     * @param ObjectManager $manager
     */
    protected function updateTranslations(ObjectManager $manager)
    {
        /** @var $manager EntityManager */
        $manager->createQuery(
            'UPDATE OroAddressBundle:CountryTranslation trans SET trans.country = trans.foreignKey'
        )->execute();
        $manager->createQuery(
            'UPDATE OroAddressBundle:RegionTranslation trans SET trans.region = trans.foreignKey'
        )->execute();
    }
}
