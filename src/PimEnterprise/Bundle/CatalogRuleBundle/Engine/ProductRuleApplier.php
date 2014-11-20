<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Engine;

use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\ApplierInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Model\LoadedRuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Applies product rules via a batch.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleApplier implements ApplierInterface
{
    //TODO: Move those actions to a dedicated class
    const SET_ACTION  = 'set_value';
    const COPY_ACTION = 'copy_value';

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ProductUpdaterInterface */
    protected $productUpdater;

    /**
     * @param ProductUpdaterInterface  $productUpdater
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ProductUpdaterInterface $productUpdater, EventDispatcherInterface $eventDispatcher)
    {
        $this->productUpdater  = $productUpdater;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(LoadedRuleInterface $rule, RuleSubjectSetInterface $subjectSet)
    {
        $this->eventDispatcher->dispatch(RuleEvents::PRE_APPLY, new SelectedRuleEvent($rule, $subjectSet));

        $actions = $rule->getActions();
        foreach ($actions as $action) {
            if (isset($action['type'])) {
                //TODO: clean all this by doing smaller methods
                //TODO: should we dispatch an event APPLY_CANCELED when an error occurs ?
                switch ($action['type']) {
                    case static::SET_ACTION:
                        $resolver = new OptionsResolver();
                        $this->configureSetValueAction($resolver);
                        $action = $resolver->resolve($action);

                        $this->productUpdater->setValue(
                            $subjectSet->getSubjects(),
                            $action['field'],
                            $action['value'],
                            $action['locale'],
                            $action['scope']
                        );
                        break;
                    case static::COPY_ACTION:
                        $resolver = new OptionsResolver();
                        $this->configureCopyValueAction($resolver);
                        $action = $resolver->resolve($action);

                        $this->productUpdater->copyValue(
                            $subjectSet->getSubjects(),
                            $action['from_field'],
                            $action['to_field'],
                            $action['from_locale'],
                            $action['to_locale'],
                            $action['from_scope'],
                            $action['to_scope']
                        );
                        break;
                    default:
                        throw new \LogicException(
                            sprintf('The action "%s" is not supported yet.', $action['type'])
                        );
                        break;
                }
            }
        }

        $this->eventDispatcher->dispatch(RuleEvents::POST_APPLY, new SelectedRuleEvent($rule, $subjectSet));
    }

    /**
     * {@inheritdoc}
     */
    public function supports(LoadedRuleInterface $rule)
    {
        return 'product' === $rule->getType();
    }

    /**
     * Configure the set value action optionResolver
     *
     * @param OptionsResolver $optionsResolver
     */
    protected function configureSetValueAction(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults(['locale' => null, 'scope'  => null]);
        $optionsResolver->setRequired(['field', 'value', 'type']);
    }

    /**
     * Configure the copy value action optionResolver
     *
     * @param OptionsResolver $optionsResolver
     */
    protected function configureCopyValueAction(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults([
            'from_locale' => null,
            'to_locale'   => null,
            'from_scope'  => null,
            'to_scope'    => null
        ]);
        $optionsResolver->setRequired(['from_field', 'to_field', 'type']);
    }
}
