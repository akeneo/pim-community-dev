<?php

namespace Akeneo\Component\Localization\Presenter;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class TranslatablePresenter
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslatablePresenter implements PresenterInterface
{
    /** @var string[] */
    protected $attributeTypes;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     * @param string[]            $attributeTypes
     */
    public function __construct(TranslatorInterface $translator, array $attributeTypes)
    {
        $this->translator     = $translator;
        $this->attributeTypes = $attributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function present($value, array $options = [])
    {
        $parameters = isset($options['parameters']) ? $options['parameters'] : [];
        $domain     = isset($options['domain']) ? $options['domain'] : [];

        return $this->translator->trans($value, $parameters, $domain);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return in_array($attributeType, $this->attributeTypes);
    }
}
