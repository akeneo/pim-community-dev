<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;

/**
 * Exclude from a product draft all unexisting attributes
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ExcludeDeletedAttributeSubscriber implements EventSubscriber
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
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

        $changes = $productDraft->getChanges();
        foreach ($changes as $type => $attributes) {
            foreach ($attributes as $code => $change) {
                $attribute = $this->attributeRepository->findOneByIdentifier($code);
                if (null === $attribute) {
                    unset($changes[$type][$code]);
                }
            }
        }

        $productDraft->setChanges($changes);
    }
}
