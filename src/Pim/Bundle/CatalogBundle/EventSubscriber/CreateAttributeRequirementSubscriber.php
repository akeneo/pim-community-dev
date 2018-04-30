<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Pim\Component\Catalog\Factory\AttributeRequirementFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

/**
 * Create attribute requirements for each family attributes after creating a channel
 * If the attribute is the identifier, then the requirement should be required
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateAttributeRequirementSubscriber implements EventSubscriber
{
    /** @var AttributeRequirementFactory */
    protected $requirementFactory;

    /**
     * Constructor
     *
     * @param AttributeRequirementFactory $requirementFactory
     */
    public function __construct(AttributeRequirementFactory $requirementFactory)
    {
        $this->requirementFactory = $requirementFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['prePersist'];
    }

    /**
     * Create requirements for each families' attributes for the newly created channel
     *
     * @param LifecycleEventArgs $event
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if (!$entity instanceof ChannelInterface) {
            return;
        }

        $entityManager = $event->getEntityManager();
        $families = $entityManager->getRepository(FamilyInterface::class)->findAll();

        if (count($families)) {
            /** @var AttributeRepositoryInterface $attributeRepository */
            $attributeRepository = $entityManager->getRepository(AttributeInterface::class);
            $identifier = $attributeRepository->getIdentifier();

            foreach ($families as $family) {
                $requirement = $this->requirementFactory->createAttributeRequirement(
                    $identifier,
                    $entity,
                    true
                );
                $requirement->setFamily($family);
                $entityManager->persist($requirement);
            }
        }
    }
}
