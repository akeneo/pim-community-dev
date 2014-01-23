<?php

namespace Pim\Bundle\CatalogBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\Factory\AttributeRequirementFactory;

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

        if (!$entity instanceof Channel) {
            return;
        }

        $entityManager = $event->getEntityManager();
        $families = $entityManager->getRepository('PimCatalogBundle:Family')->findAll();

        foreach ($families as $family) {
            foreach ($family->getAttributes() as $attribute) {
                $requirement = $this->requirementFactory->createAttributeRequirement(
                    $attribute,
                    $entity,
                    'pim_catalog_identifier' === $attribute->getAttributeType()
                );
                $requirement->setFamily($family);
                $entityManager->persist($requirement);
            }
        }
    }
}
