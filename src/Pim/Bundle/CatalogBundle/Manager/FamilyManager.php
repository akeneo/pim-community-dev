<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;

/**
 * Family manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated not used anymore, will be removed in 1.5
 */
class FamilyManager
{
    /** @var FamilyRepositoryInterface */
    protected $repository;

    /** @var UserContext */
    protected $userContext;

    /**
     * Constructor
     *
     * @param FamilyRepositoryInterface $repository
     * @param UserContext               $userContext
     */
    public function __construct(
        FamilyRepositoryInterface $repository,
        UserContext $userContext
    ) {
        $this->repository      = $repository;
        $this->userContext     = $userContext;
    }

    /**
     * Get choices, only used by datagrids, should be moved
     *
     * @deprecated not used anymore, will be removed in 1.5
     *
     * @return array
     */
    public function getChoices()
    {
        return $this->repository->getChoices(
            ['localeCode' => $this->userContext->getCurrentLocaleCode()]
        );
    }
}
