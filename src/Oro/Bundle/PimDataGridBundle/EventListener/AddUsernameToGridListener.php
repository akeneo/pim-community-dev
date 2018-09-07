<?php

namespace Oro\Bundle\PimDataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AddUsernameToGridListener
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function onBuildAfter(BuildAfter $event)
    {
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
