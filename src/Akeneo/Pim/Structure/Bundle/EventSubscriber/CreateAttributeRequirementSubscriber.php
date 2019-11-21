<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Factory\AttributeRequirementFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Pim\Structure\Component\Model\Family;
use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

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

    /** @var Connection */
    private $dbConnection;

    public function __construct(AttributeRequirementFactory $requirementFactory, Connection $dbConnection)
    {
        $this->requirementFactory = $requirementFactory;
        $this->dbConnection = $dbConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [Events::postPersist];
    }

    /**
     * Create requirements for each families' attributes for the newly created channel
     *
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if (!$entity instanceof ChannelInterface) {
            return;
        }

        $entityManager = $event->getEntityManager();
        $families = $entityManager->getRepository(Family::class)->findAll();

        foreach ($families as $family) {
            $familyRequirements = [];
            foreach ($family->getAttributes() as $attribute) {
                $requirement = $this->requirementFactory->createAttributeRequirement(
                    $attribute,
                    $entity,
                    AttributeTypes::IDENTIFIER === $attribute->getType()
                );
                $requirement->setFamily($family);
                $familyRequirements[] = $requirement;
            }
            $this->persistAttributeRequirements($familyRequirements);
        }
    }

    private function persistAttributeRequirements(array $attributeRequirements): void
    {
        if (empty($attributeRequirements)) {
            return;
        }

        $values = array_map(function (AttributeRequirementInterface $attributeRequirement) {
            return sprintf(
                '(%d,%d,%d,%d)',
                $attributeRequirement->getFamily()->getId(),
                $attributeRequirement->getAttribute()->getId(),
                $attributeRequirement->getChannel()->getId(),
                $attributeRequirement->isRequired()
            );
        }, $attributeRequirements);

        $sql = sprintf(
            'INSERT INTO pim_catalog_attribute_requirement (family_id, attribute_id, channel_id, required) VALUES %s',
            implode(',', $values)
        );

        $this->dbConnection->executeQuery($sql);
    }
}
