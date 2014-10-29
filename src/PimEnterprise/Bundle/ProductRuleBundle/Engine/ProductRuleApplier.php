<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductRuleBundle\Engine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\ApplierInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Applies product rules via a batch.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleApplier implements ApplierInterface
{
    const SET_ACTION_LABEL  = 'set_value';
    const COPY_ACTION_LABEL = 'copy_value';

    /** @var EventDispatcher */
    protected $eventDispatcher;

    /** @var ProductUpdaterInterface */
    protected $productUpdater;

    /**
     * @param ProductUpdaterInterface $productUpdater
     * @param EventDispatcher         $eventDispatcher
     */
    public function __construct(ProductUpdaterInterface $productUpdater, EventDispatcher $eventDispatcher)
    {
        $this->productUpdater  = $productUpdater;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(RuleInterface $rule, RuleSubjectSetInterface $subjectSet)
    {
        $this->eventDispatcher->dispatch(RuleEvents::PRE_APPLY, new SelectedRuleEvent($rule, $subjectSet));

        $start = microtime(true);

        $actions = $rule->getActions();
        foreach ($actions as $action) {
            if (isset($action['type'])) {
                switch ($action['type']) {
                    case static::SET_ACTION_LABEL:
                        $resolver = new OptionsResolver();
                        $this->configureSetValueAction($resolver);

                        $action = $resolver->resolve($action);;

                        $this->productUpdater->setValue(
                            $subjectSet->getSubjects(),
                            $action['field'],
                            $action['value'],
                            $action['locale'],
                            $action['scope']
                        );
                        break;
                    case static::COPY_ACTION_LABEL:
                        $resolver = new OptionsResolver();
                        $this->configureCopyValueAction($resolver);

                        $action = $resolver->resolve($action);;

                        $this->productUpdater->setValue(
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
                        throw new \InvalidArgumentException(sprintf('The action %s is not supported yet.', $action['type']));
                        break;
                }
            }
        }

        $this->eventDispatcher->dispatch(RuleEvents::POST_APPLY, new SelectedRuleEvent($rule, $subjectSet));
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RuleInterface $rule, RuleSubjectSetInterface $subjectSet)
    {
        return 'product' === $subjectSet->getType() &&
            $rule instanceof LoadedRule;
    }

    protected function configureSetValueAction(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults(['locale' => null, 'scope'  => null]);

        $optionsResolver->setRequired(['field', 'value', 'type']);
    }

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
