<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Translator;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Decorates the symfony translator to be able to returns translation key instead of error during transchoice.
 * @see PIM-8334
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslatorDecorator implements TranslatorInterface
{
    /** @var TranslatorInterface */
    private $symfonyTranslator;

    public function __construct(TranslatorInterface $symfonyTranslator)
    {
        $this->symfonyTranslator = $symfonyTranslator;
    }

    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->symfonyTranslator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        try {
            return $this->symfonyTranslator->transChoice($id, $number, $parameters, $domain, $locale);
        } catch (\Exception $exception) {
            return (string) $id . ': ' . (string) $number;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->symfonyTranslator->setLocale($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->symfonyTranslator->getLocale();
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations(array $domains = [], $locale = null)
    {
        return $this->symfonyTranslator->getTranslations($domains, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getFallbackLocales()
    {
        return $this->symfonyTranslator->getFallbackLocales();
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalogue($locale = null)
    {
        return $this->symfonyTranslator->getCatalogue($locale);
    }
}
