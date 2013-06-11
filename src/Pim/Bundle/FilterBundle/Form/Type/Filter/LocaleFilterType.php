<?php

namespace Pim\Bundle\FilterBundle\Form\Type\Filter;

use Pim\Bundle\ConfigBundle\Manager\LocaleManager;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;

/**
 * Overriding of ChoiceFilterType
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LocaleFilterType extends ChoiceFilterType
{

    /**
     * @staticvar string
     */
    const NAME = 'pim_type_locale_filter';

    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * @param TranslatorInterface $translator
     * @param LocaleManager       $localeManager
     */
    public function __construct(TranslatorInterface $translator, LocaleManager $localeManager)
    {
        parent::__construct($translator);

        $this->localeManager = $localeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return ChoiceFilterType::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $codes = $this->localeManager->getActiveCodesWithUserLocale();
        $localeChoices = array_combine($codes, $codes);

        $resolver->setDefaults(
            array(
                'field_type' => 'choice',
                'field_options' => array('choices' => $localeChoices)
            )
        );
    }
}
