<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Translator;

use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

/**
 * Decorates the symfony translator to be able to returns translation key instead of error during transchoice.
 * @see PIM-8334
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslatorDecorator implements TranslatorInterface, LocaleAwareInterface, TranslatorBagInterface
{
    private TranslatorInterface $symfonyTranslator;

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
     * Deprecated since Symfony 4.2, will be removed in Symfony 5.
     * Use trans instead, see Symfony\Contracts\Translation\TranslatorInterface
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        try {
            $parameters['%count%'] = $number;

            return $this->symfonyTranslator->trans($id, $parameters, $domain, $locale);
        } catch (\Exception $exception) {
            return (string) $id . ': ' . (string) $number;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        Assert::implementsInterface($this->symfonyTranslator, LocaleAwareInterface::class);

        $this->symfonyTranslator->setLocale($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        Assert::implementsInterface($this->symfonyTranslator, LocaleAwareInterface::class);

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
        Assert::implementsInterface($this->symfonyTranslator, TranslatorBagInterface::class);

        return $this->symfonyTranslator->getCatalogue($locale);
    }
}
