<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\CatalogBundle\Entity\Group;

/**
 * Valid group creation (or update) processor
 *
 * Allow to bind input data to an group and validate it
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidGroupCreationProcessor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /**
     * Entity manager
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Property for storing data during execution
     *
     * @var ArrayCollection
     */
    protected $data;

    /**
     * Property for storing valid groups during execution
     *
     * @var ArrayCollection
     */
    protected $groups;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * Constructor
     *
     * @param EntityManager      $entityManager
     * @param ValidatorInterface $validator
     */
    public function __construct(
        EntityManager $entityManager,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->validator     = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }

    /**
     * Receives an array of groups and processes them
     *
     * @param mixed $data Data to be processed
     *
     * @return Group[]
     */
    public function process($data)
    {
        $this->data   = new ArrayCollection($data);
        $this->groups = new ArrayCollection();

        foreach ($this->data as $item) {
            $this->processItem($item);
        }

        return $this->groups->toArray();
    }

    /**
     * If the group is valid, it is stored into the groups property
     *
     * @param array $item
     */
    private function processItem($item)
    {
        $group = $this->getGroup($item);

        foreach ($item as $key => $value) {
            if (preg_match('/^label-(.+)/', $key, $matches)) {
                $group->setLocale($matches[1]);
                $group->setLabel($value);
            }
        }

        $group->setLocale(null);

        $violations = $this->validator->validate($group);
        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->stepExecution->addError((string) $violation);
            }

            return;
        } else {
            $this->associations[] = $association;
        }
    }

    /**
     * Create an group or get it if already exists
     *
     * @param array $item
     *
     * @return Group
     */
    private function getGroup(array $item)
    {
        $group = $this->findGroup($item['code']);
        if (!$group) {
            $group = new Group();
            $group->setCode($item['code']);

            $groupType = $this->getGroupType($item);
            $group->setType($groupType);

            if ($group->getType()->isVariant()) {
                $axis = $this->getAxis($item);
                $group->setAttributes($axis);
            }
        }

        return $group;
    }

    /**
     * Find group by code
     *
     * @param string $code
     *
     * @return Group|null
     */
    private function findGroup($code)
    {
        return $this
            ->entityManager
            ->getRepository('PimCatalogBundle:Group')
            ->findOneBy(array('code' => $code));
    }

    /**
     * Find the group type from its code
     *
     * @param array $item
     *
     * @return GroupType null
     */
    private function getGroupType(array $item)
    {
        return $this
            ->entityManager
            ->getRepository('PimCatalogBundle:GroupType')
            ->findOneBy(array('code' => $item['type']));
    }

    /**
     * Get group axis
     *
     * @param array $item
     *
     * @return ProductAttribute[]
     */
    private function getAxis(array $item)
    {
        $attributeCodes = explode(',', $item['attributes']);
        $attributeCodes = array_unique($attributeCodes);
        $attributes = array();

        if (count($attributeCodes) > 0) {
            foreach ($attributeCodes as $attributeCode) {
                $attribute = $this->findAttribute($attributeCode);
                if ($attribute) {
                    $attributes[] = $attribute;
                }
            }
        }

        return $attributes;
    }

    /**
     * Find the attribute from its code
     *
     * @param string $code
     *
     * @return ProductAttribute
     */
    private function findAttribute($code)
    {
        return $this
            ->entityManager
            ->getRepository('PimCatalogBundle:ProductAttribute')
            ->findOneBy(array('code' => $attributeCode));
    }
}
