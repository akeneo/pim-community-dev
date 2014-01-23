<?php

namespace Pim\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

/**
 * Overriding of ChoiceFilterType
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopeFilterType extends ChoiceFilterType
{
    /**
     * @staticvar string
     */
    const NAME = 'pim_type_scope_filter';

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @param TranslatorInterface $translator
     * @param ChannelManager      $channelManager
     */
    public function __construct(TranslatorInterface $translator, ChannelManager $channelManager)
    {
        parent::__construct($translator);

        $this->channelManager = $channelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceFilterType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $scopeChoices = $this->channelManager->getChannelChoiceWithUserChannel();

        $resolver->setDefaults(
            [
                'field_type' => 'choice',
                'field_options' => ['choices' => $scopeChoices]
            ]
        );
    }
}
