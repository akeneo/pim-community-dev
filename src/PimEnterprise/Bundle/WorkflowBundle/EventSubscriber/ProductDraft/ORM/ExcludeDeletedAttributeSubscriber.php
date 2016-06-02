<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\ORM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;

/**
 * Exclude from a product draft all unexisting attributes
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ExcludeDeletedAttributeSubscriber implements EventSubscriber
{
    /** @var string */
    protected $attributeClassName;

    /**
     * @param string $attributeClassName
     */
    public function __construct($attributeClassName)
    {
        $this->attributeClassName = $attributeClassName;
    }

    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return ['postLoad'];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $productDraft = $args->getObject();

        if (!$productDraft instanceof ProductDraftInterface) {
            return;
        }

        $attributeRepository = $args->getObjectManager()->getRepository($this->attributeClassName);

        $changes = $productDraft->getChanges();
        foreach ($changes as $type => $attributes) {
            foreach ($attributes as $code => $change) {
                $attribute = $attributeRepository->findOneByIdentifier($code);
                if (null === $attribute) {
                    unset($changes[$type][$code]);
                }
            }
        }

        $productDraft->setChanges($changes);
    }
}
