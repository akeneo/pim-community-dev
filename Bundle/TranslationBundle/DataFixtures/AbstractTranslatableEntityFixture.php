<?php

namespace Oro\Bundle\TranslationBundle\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Finder\Finder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

abstract class AbstractTranslatableEntityFixture extends AbstractFixture implements ContainerAwareInterface
{
    const ENTITY_DOMAIN      = 'entities';
    const DOMAIN_FILE_REGEXP = '/^%domain%\.(.*?)\./';

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
     * @var array
     */
    protected $translationLocales;

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->translator = $this->container->get('translator');
        $this->loadEntities($manager);
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Get regexp for current entity domain
     *
     * @return string
     */
    protected function getDomainFileRegExp()
    {
        return str_replace('%domain%', preg_quote(static::ENTITY_DOMAIN), static::DOMAIN_FILE_REGEXP);
    }

    /**
     * Get list of existing translation locales for current translation domain
     *
     * @return array
     */
    protected function getTranslationLocales()
    {
        if (null === $this->translationLocales) {
            $translationDirectory = str_replace('/', DIRECTORY_SEPARATOR, $this->translationDirectory);
            $translationDirectories = array();

            foreach ($this->container->getParameter('kernel.bundles') as $bundle) {
                $reflection = new \ReflectionClass($bundle);
                $bundleTranslationDirectory = dirname($reflection->getFilename()) . $translationDirectory;
                if (is_dir($bundleTranslationDirectory) && is_readable($bundleTranslationDirectory)) {
                    $translationDirectories[] = realpath($bundleTranslationDirectory);
                }
            }

            $domainFileRegExp = $this->getDomainFileRegExp();

            $finder = new Finder();
            $finder->in($translationDirectories)->name($domainFileRegExp);

            $this->translationLocales = array();
            /** @var $file \SplFileInfo */
            foreach ($finder as $file) {
                preg_match($domainFileRegExp, $file->getFilename(), $matches);
                if ($matches) {
                    $this->translationLocales[] = $matches[1];
                }
            }
        }

        return $this->translationLocales;
    }

    /**
     * Translate string based on input parameters
     *
     * @param string $id
     * @param string $prefix
     * @param string $locale
     * @param array $parameters
     * @param string $domain
     * @return string
     */
    protected function translate($id, $prefix = null, $locale = null, $parameters = array(), $domain = null)
    {
        if (!$domain) {
            $domain = static::ENTITY_DOMAIN;
        }

        $translationId = $this->getTranslationId($id, $prefix);

        return $this->translator->trans($translationId, $parameters, $domain, $locale);
    }

    /**
     * Get translation ID based on source ID and prefix
     *
     * @param string $id
     * @param string $prefix
     * @return string
     */
    protected function getTranslationId($id, $prefix = null)
    {
        $prefixString = $prefix ? $prefix . '.' : '';

        return $prefixString . $id;
    }

    /**
     * Load entities to DB
     *
     * @param ObjectManager $manager
     */
    abstract protected function loadEntities(ObjectManager $manager);
}
