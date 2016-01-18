<?php

namespace Pim\Bundle\ConnectorBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;

/**
 * Implementation of JobConfigurationRepository
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobConfigurationRepository extends EntityRepository implements JobConfigurationRepositoryInterface
{
}
