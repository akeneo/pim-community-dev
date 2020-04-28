<?php

namespace Oro\Bundle\PimDataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AddUsernameToGridListener
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage, SecurityFacade $securityFacade)
    {
        $this->tokenStorage = $tokenStorage;
        $this->securityFacade = $securityFacade;
    }

    public function onBuildAfter(BuildAfter $event)
    {
        if ($this->securityFacade->isGranted('pim_enrich_job_tracker_view_all_jobs')) {
            return;
        }
        $dataSource = $event->getDatagrid()->getDatasource();

        $token = $this->tokenStorage->getToken();
        $user = null !== $token ? $token->getUsername() : null;

        $parameters = $dataSource->getParameters();
        $parameters['user'] = $user;
        $dataSource->setParameters($parameters);

        $qb = $dataSource->getQueryBuilder();
        $qb->andWhere($qb->expr()->eq('e.user', ':user'));
    }
}
