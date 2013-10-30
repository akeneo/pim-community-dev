<?php

namespace Oro\Bundle\UserBundle\EventListener;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\ImapBundle\Sync\ImapEmailSynchronizer;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\EmailBundle\Datagrid\EmailQueryFactory;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;

class UserEmailGridListener
{
    /** @var  EmailQueryFactory */
    protected $queryFactory;

    /** @var  EntityManager */
    protected $em;

    /** @var RequestParameters */
    protected $requestParams;

    /** @var ImapEmailSynchronizer */
    protected $imapSync;

    public function __construct(
        EntityManager $em,
        EmailQueryFactory $factory,
        RequestParameters $requestParameters
    ) {
        $this->em      = $em;
        $this->queryFactory = $factory;
        $this->requestParams = $requestParameters;
        //$this->imapSync = $imapSync;
    }

    public function setEmailSync(ImapEmailSynchronizer $emailSync)
    {
        $this->imapSync = $emailSync;
    }

    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            /** @var QueryBuilder $query */
            $queryBuilder = $datasource->getQuery();

            $this->queryFactory->prepareQuery($queryBuilder);

            if ($id = $this->requestParams->get('userId')) {
                $user = $this->em
                    ->getRepository('OroUserBundle:User')
                    ->find($id);

                // TODO: select imap configuration by userId
                $origin = $user->getImapConfiguration();
                $originId = $origin !== null ? $origin->getId() : null;

                if (array_key_exists(
                    'refresh',
                    $this->requestParams->get(RequestParameters::ADDITIONAL_PARAMETERS)
                ) && $originId) {
                    $this->imapSync->syncOrigins(array($originId));
                }
            } else {
                $originId = null;
            }

            $queryBuilder->setParameter('origin_id', $originId);
        }
    }
}
