<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Export;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionMediatorInterface;

/**
 * Export action handler
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportMassActionHandler implements MassActionHandlerInterface
{
    /**
     * @var string $exportFormat
     */
    protected $exportFormat;

    /**
     * Return the datagrid QueryBuilder to use for quick export
     *
     * @param MassActionMediatorInterface $mediator
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function handle(MassActionMediatorInterface $mediator)
    {
        $qb = $mediator->getResults()->getSource();

        $rootAlias = current($qb->getRootAliases());
        $qb
            ->resetDQLParts(array('select', 'from'))
            ->select($rootAlias)
            ->from('Pim\Bundle\CatalogBundle\Model\Product', $rootAlias);

        return $qb->getQuery()->execute();
    }
}
