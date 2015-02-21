<?php

namespace Pim\Bundle\DataGridBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\DataGridBundle\Datagrid\Manager as DatagridManager;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;

/**
 * Datagrid view manager
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridViewManager implements SaverInterface, RemoverInterface
{
    /** @var EntityRepository */
    protected $repository;

    /** @var DatagridManager */
    protected $datagridManager;

    /** @var ObjectManager */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param EntityRepository $repository
     * @param DatagridManager  $datagridManager
     * @param ObjectManager    $objectManager
     */
    public function __construct(
        EntityRepository $repository,
        DatagridManager $datagridManager,
        ObjectManager $objectManager
    ) {
        $this->repository      = $repository;
        $this->datagridManager = $datagridManager;
        $this->objectManager   = $objectManager;
    }

    /**
     * Returns all public views
     *
     * @param string $alias
     *
     * @return DatagridView
     */
    public function findPublic($alias)
    {
        return $this->repository->findBy(
            [
                'datagridAlias' => $alias,
                'type'          => DatagridView::TYPE_PUBLIC
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
        if (!$object instanceof DatagridView) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a Pim\Bundle\DataGridBundle\Entity\DatagridView, "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $options = array_merge(['flush' => true], $options);
        $this->objectManager->persist($object);
        if ($options['flush']) {
            $this->objectManager->flush($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object, array $options = [])
    {
        if (!$object instanceof DatagridView) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a Pim\Bundle\DataGridBundle\Entity\DatagridView, "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $options = array_merge(['flush' => true], $options);
        $this->objectManager->remove($object);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }
    }

    /**
     * Get datagrid column choices for the provided datagrid alias
     *
     * @param string  $alias
     * @param boolean $displayedColumns
     *
     * @return array
     */
    public function getColumnChoices($alias, $displayedColumns = false)
    {
        $choices = array();

        $path = (true === $displayedColumns) ?
            sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY) :
            sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::AVAILABLE_COLUMNS_KEY);

        $columnsConfig = $this
            ->datagridManager
            ->getDatagrid($alias)
            ->getAcceptor()
            ->getConfig()
            ->offsetGetByPath($path);

        if ($columnsConfig) {
            $properties = ['label', 'sortOrder', 'group', 'groupOrder'];

            foreach ($columnsConfig as $code => $meta) {
                $choice = ['code' => $code];
                foreach ($properties as $property) {
                    if (isset($meta[$property])) {
                        $choice[$property] = $meta[$property];
                    }
                }
                $choices[$code] = $choice;
            }
        }

        return $choices;
    }
}
