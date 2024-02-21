<?php

namespace Oro\Bundle\PimDataGridBundle\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;

/**
 * Update the datagrid view properties
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridViewUpdater implements ObjectUpdaterInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $userRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $userRepository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function update($datagridView, array $data, array $options = [])
    {
        if (!$datagridView instanceof DatagridView) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($datagridView),
                DatagridView::class
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($datagridView, $field, $value);
        }

        return $this;
    }

    /**
     * Set the value to an object property
     *
     * @param DatagridView $datagridView
     * @param string       $field
     * @param mixed        $value
     */
    protected function setData(DatagridView $datagridView, $field, $value)
    {
        switch ($field) {
            case 'label':
                $datagridView->setLabel($value);
                break;
            case 'owner':
                $user = $this->userRepository->findOneByIdentifier($value);
                $datagridView->setOwner($user);
                break;
            case 'datagrid_alias':
                $datagridView->setDatagridAlias($value);
                break;
            case 'type':
                $datagridView->setType($value);
                break;
            case 'columns':
                $datagridView->setColumns(array_map('trim', explode(',', $value)));
                break;
            case 'filters':
                $datagridView->setFilters($value);
                break;
        }
    }
}
