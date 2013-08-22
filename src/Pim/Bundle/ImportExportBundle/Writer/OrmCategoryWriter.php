<?php

namespace Pim\Bundle\ImportExportBundle\Writer;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\BatchBundle\Item\ItemWriterInterface;
use Pim\Bundle\ImportExportBundle\AbstractConfigurableStepElement;

/**
 * Category writer using ORM method
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmCategoryWriter extends AbstractConfigurableStepElement implements ItemWriterInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager  = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        if (is_array(reset($items))) {
            $items = call_user_func_array('array_merge', $items);
        }

        foreach ($items as $category) {
            $this->entityManager->persist($category);
        }

        $this->entityManager->flush();

        $this->entityManager->clear('Oro\\Bundle\\SearchBundle\\Entity\\Item');
        $this->entityManager->clear('Oro\\Bundle\\SearchBundle\\Entity\\IndexText');
    }
}
