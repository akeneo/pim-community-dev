<?php

namespace PimEnterprise\Bundle\EnrichBundle\View\Tab;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\EnrichBundle\View\Tab\TabInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Simple tab rendering a template checking if the tab is accepted by voters
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VoterTab implements TabInterface
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var TabInterface */
    protected $tab;

    /** @var array */
    protected $voterOptions;

    /**
     * @param SecurityFa* @param TabInterfacecade $securityFacade
        $tab
     * @param array          $voterOptions
     */
    public function __construct(
        SecurityFacade $securityFacade,
        TabInterface $tab,
        array $voterOptions
    ) {
        $this->tab            = $tab;
        $this->securityFacade = $securityFacade;
        $this->voterOptions   = $voterOptions;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->voterOptions = $resolver->resolve($voterOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(array $context = [])
    {
        return $this->tab->getContent($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(array $context = [])
    {
        return $this->tab->getTitle($context);
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible(array $context = [])
    {
        return $this->securityFacade->isGranted(
                constant($this->voterOptions['attribute']),
                $context[$this->voterOptions['entity']]
            ) &&
            $this->tab->isVisible($context);
    }

    /**
     * Option resolver configuration
     * @param OptionsResolverInterface $resolver
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('attribute', 'entity'));
    }
}
