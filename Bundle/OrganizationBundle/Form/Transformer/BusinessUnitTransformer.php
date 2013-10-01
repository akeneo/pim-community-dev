<?php

namespace Oro\Bundle\OrganizationBundle\Form\Transformer;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Exception\TransformationFailedException;

use Symfony\Component\Form\DataTransformerInterface;

use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Manager\BusinessUnitManager;

class BusinessUnitTransformer implements DataTransformerInterface
{
    /**
     * @var BusinessUnitManager
     */
    protected $manager;

    /**
     * @var BusinessUnit
     */
    protected $entity;

    public function __construct(BusinessUnitManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param mixed $id
     * @return null|BusinessUnit
     * @throws TransformationFailedException
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $businessUnit = $this->manager->getBusinessUnitRepo()
            ->findOneBy(array('id' => $id));

        if (null === $businessUnit) {
            throw new TransformationFailedException(
                sprintf('Business Unit with id "%s" does not exist.', $id)
            );
        }

        return $businessUnit;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return "";
        }

        return $value->getId();
    }

    /**
     * Setter for entity object
     *
     * @param BusinessUnit $entity
     */
    public function setEntity(BusinessUnit $entity)
    {
        $this->entity = $entity;
    }
}
