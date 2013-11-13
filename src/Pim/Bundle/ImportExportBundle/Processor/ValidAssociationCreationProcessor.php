<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\CatalogBundle\Entity\Association;

/**
 * Valid association creation (or update) processor
 *
 * Allow to bind input data to an association and validate it
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidAssociationCreationProcessor extends AbstractConfigurableStepElement implements
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
     * Property for storing valid associations during execution
     *
     * @var ArrayCollection
     */
    protected $associations;

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
     * Receives an array of associations and processes them
     *
     * @param mixed $data Data to be processed
     *
     * @return Association[]
     */
    public function process($data)
    {
        $this->data = new ArrayCollection($data);
        $this->associations = new ArrayCollection();

        foreach ($this->data as $item) {
            $this->processItem($item);
        }

        return $this->associations->toArray();
    }

    /**
     * If the association is valid, it is stored into the associations property
     *
     * @param array $item
     */
    private function processItem($item)
    {
        $association = $this->getAssociation($item);

        $association->setCode($item['code']);
        foreach ($item as $key => $value) {
            if (preg_match('/^label-(.+)/', $key, $matches)) {
                $association->setLocale($matches[1]);
                $association->setLabel($value);
            }
        }

        $association->setLocale(null);

        $violations = $this->validator->validate($association);
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
     * Create an association or get it if already exists
     *
     * @param array $item
     *
     * @return Association
     */
    private function getAssociation(array $item)
    {
        $association = $this->findAssociation($item['code']);
        if (!$association) {
            $association = new Association();
        }

        return $association;
    }

    /**
     * Find association by code
     *
     * @param string $code
     *
     * @return Association|null
     */
    private function findAssociation($code)
    {
        return $this
            ->entityManager
            ->getRepository('PimCatalogBundle:Association')
            ->findOneBy(array('code' => $code));
    }
}
