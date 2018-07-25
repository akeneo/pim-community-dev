<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Purger;

use Akeneo\Tool\Component\Versioning\Model\VersionInterface;

/**
 * Prevents first version of an entity from being purged
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SkipFirstVersionPurgerAdvisor implements VersionPurgerAdvisorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(VersionInterface $version)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isPurgeable(VersionInterface $version, array $options)
    {
        return 1 !== $version->getVersion();
    }
}
