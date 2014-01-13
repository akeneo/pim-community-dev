<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Factory\FamilyFactory;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Guesser\GuesserInterface;

/**
 * Family transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyTransformer extends NestedEntityTransformer
{
    /**
     * @var FamilyFactory
     */
    protected $factory;

    /**
     * @var string
     */
    protected $requirementClass;

    /**
     * Constructor
     *
     * @param RegistryInterface              $doctrine
     * @param PropertyAccessorInterface      $propertyAccessor
     * @param GuesserInterface               $guesser
     * @param ColumnInfoTransformerInterface $columnInfoTransformer
     * @param EntityTransformerInterface     $transformerRegistry
     * @param FamilyFactory                  $factory
     * @param string                         $requirementClass
     */
    public function __construct(
        RegistryInterface $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $columnInfoTransformer,
        EntityTransformerInterface $transformerRegistry,
        FamilyFactory $factory,
        $requirementClass
    ) {
        parent::__construct($doctrine, $propertyAccessor, $guesser, $columnInfoTransformer, $transformerRegistry);
        $this->factory = $factory;
        $this->requirementClass = $requirementClass;
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($class, array $data)
    {
        return $this->factory->createFamily();
    }

    /**
     * {@inheritdoc}
     */
    protected function setProperties($class, $entity, array $data)
    {
        if (isset($data['requirements'])) {
            $requirementsData = $data['requirements'];
            unset($data['requirements']);
        }

        parent::setProperties($class, $entity, $data);

        if (isset($requirementsData)) {
            $this->setRequirements($class, $entity, $requirementsData);
        }
    }

    /**
     * Sets the requirements
     *
     * @param string $class
     * @param Family $family
     * @param array  $requirementsData
     */
    protected function setRequirements($class, Family $family, array $requirementsData)
    {
        foreach ($requirementsData as $channelCode => $attributeCodes) {
            $this->setChannelRequirements($class, $family, $channelCode, $attributeCodes);
        }
    }

    /**
     * Sets the requirements for a channel
     *
     * @param string $class
     * @param Family $family
     * @param string $channelCode
     * @param array  $attributeCodes
     */
    protected function setChannelRequirements($class, Family $family, $channelCode, $attributeCodes)
    {
        foreach ($attributeCodes as $attributeCode) {
            $data = array(
                'attribute' => $attributeCode,
                'channel'   => $channelCode,
                'required'  => true
            );
            $requirement = $this->transformNestedEntity($class, 'requirements', $this->requirementClass, $data);

            $family->addAttributeRequirement($requirement);
        }
    }
}
