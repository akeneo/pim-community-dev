<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Factory\FamilyFactory;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;


/**
 * Family transformer
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyTransformer extends ORMTransformer
{
    /**
     * @var FamilyFactory
     */
    protected $factory;

    /**
     * @var string
     */
    protected $familyClass;

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
     * @param FamilyFactory                  $factory
     * @param string                         $familyClass
     * @param string                         $requirementClass
     */
    public function __construct(
        RegistryInterface $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser, 
        ColumnInfoTransformerInterface $columnInfoTransformer,
        FamilyFactory $factory,
        $familyClass,
        $requirementClass
    ) {
        parent::__construct($doctrine, $propertyAccessor, $guesser, $columnInfoTransformer);
        $this->factory = $factory;
        $this->familyClass = $familyClass;
        $this->requirementClass = $requirementClass;
    }

    /**
     * {@inheritdoc}
     */
    protected function create($class)
    {
        return ($this->familyClass === $class) ? $this->factory->createFamily() : parent::create($class);
    }

    /**
     * {@inheritdoc}
     */
    protected function setProperties($class, $entity, array $data)
    {
        if ($this->familyClass === $class && isset($data['requirements'])) {
            $requirementsData = $data['requirements'];
            unset($data['requirements']);
            parent::setProperties($class, $entity, $data);
            $this->setRequirements($entity, $requirementsData);

        } else {
            parent::setProperties($class, $entity, $data);
        }
    }

    /**
     * Sets the requirements
     * 
     * @param Family $family
     * @param array  $requirementsData
     */
    protected function setRequirements(Family $family, array $requirementsData)
    {
        $requirements = array();
        foreach ($requirementsData as $channelCode => $attributeCodes) {
            $requirements = array_merge(
                $requirements,
                $this->getRequirements($channelCode, $attributeCodes)
            );
            if (count($this->errors)) {
                break;
            }
        }
        $family->setAttributeRequirements($requirements);
    }

    /**
     * Returns the requirements for a channel
     *
     * @param  string $channelCode
     * @param  array  $attributeCodes
     * 
     * @return AttributeRequirement[]
     */
    protected function getRequirements($channelCode, $attributeCodes)
    {
        $requirements = array();
        foreach ($attributeCodes as $attributeCode) {
            $data = array(
                'attribute' => $attributeCode,
                'channel'   => $channelCode,
                'required'  => true
            );
            $requirement = $this->getEntity($this->requirementClass, array());
            $this->setProperties($this->requirementClass, $requirement, $data);
            if (count($this->errors)) {
                break;
            }

            $requirements[] = $requirement;
        }

        return $requirements;
    }
}
