<?php

namespace Pim\Bundle\TranslationBundle\Twig;

use Akeneo\Component\Console\CommandLauncher;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Translations twig extension.
 *
 * This extension retrieves the current locale JS translations file path.
 * If the translations file doesn't exist, it call the translation dumper to create it.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationsExtension extends \Twig_Extension
{
    /** @var CommandLauncher */
    protected $commandLauncher;

    /** @var RequestStack */
    protected $requestStack;

    /** @var string */
    protected $asseticRoot;

    /**
     * @param CommandLauncher $commandLauncher
     * @param RequestStack    $requestStack
     * @param string          $asseticRoot
     */
    public function __construct(
        CommandLauncher $commandLauncher,
        RequestStack $requestStack,
        $asseticRoot
    ) {
        $this->commandLauncher = $commandLauncher;
        $this->requestStack    = $requestStack;
        $this->asseticRoot     = $asseticRoot;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('get_translations_file', [$this, 'getTranslationsFile'])
        ];
    }

    /**
     * Get the absolute filepath for JS translations.
     * If the file doesn't exist, it creates it.
     *
     * @return string
     */
    public function getTranslationsFile()
    {
        $localeCode = $this->getLocale();
        $translationFilePath = sprintf('%s/js/translation/%s.js', $this->asseticRoot, $localeCode);
        $translationFilePath = realpath($translationFilePath);

        if (!file_exists($translationFilePath)) {
            $result = $this->commandLauncher->executeForeground(sprintf('oro:translation:dump %s', $localeCode));

            if ($result->getCommandStatus() > 0) {
                throw new \RuntimeException(
                    sprintf('Error during translations file generation for locale "%s"', $localeCode)
                );
            }
        }

        return $translationFilePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_translations_extension';
    }

    /**
     * Get user's locale
     *
     * @return string
     */
    protected function getLocale()
    {
        $request = $this->requestStack->getMasterRequest();
        if (null === $request) {
            return 'en';
        }

        return $request->getLocale();
    }
}
