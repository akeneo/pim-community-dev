<?php

namespace Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker;

use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * Checks if a view element is visible according to an ACL.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AclVisibilityChecker implements VisibilityCheckerInterface
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /**
     * @param SecurityFacade $securityFacade
     */
    public function __construct(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible(array $config = [], array $context = [])
    {
        if (!isset($config['acl'])) {
            throw new \InvalidArgumentException('The "acl" should be provided in the configuration.');
        }

        return $this->securityFacade->isGranted($config['acl']);
    }
}
