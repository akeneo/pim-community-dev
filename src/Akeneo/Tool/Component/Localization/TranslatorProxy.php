<?php

namespace Akeneo\Tool\Component\Localization;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Symfony Translator proxy for the Localization component.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslatorProxy
{
    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function trans($value, array $options = [])
    {
        $parameters = isset($options['parameters']) ? $options['parameters'] : [];
        $domain = isset($options['domain']) ? $options['domain'] : null;

        return $this->translator->trans($value, $parameters, $domain);
    }
}
