<?php

namespace Pim\Bundle\DataGridBundle\Manager;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\DataGridBundle\Datagrid\Manager as DatagridManager;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
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

    /** @var SaverInterface */
    protected $saver;

    /** @var RemoverInterface */
    protected $remover;

    /**
     * Constructor
     *
     * @param EntityRepository $repository
     * @param DatagridManager  $datagridManager
     * @param SaverInterface   $saver
     * @param RemoverInterface $remover
     */
    public function __construct(
        EntityRepository $repository,
        DatagridManager $datagridManager,
        SaverInterface $saver,
        RemoverInterface $remover
    ) {
        $this->repository      = $repository;
        $this->datagridManager = $datagridManager;
        $this->saver           = $saver;
        $this->remover         = $remover;
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
     *
     * @deprecated will be removed in 1.5 please use SaverInterface::save
     */
    public function save($object, array $options = [])
    {
        $this->saver->save($object, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated will be removed in 1.5 please use RemoverInterface::remove
     */
    public function remove($object, array $options = [])
    {
        $this->remover->remove($object, $options);
    }

    /**
     * Get datagrid column choices for the provided datagrid alias
     *
     * @param string $alias
     * @param bool   $displayedColumns
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
