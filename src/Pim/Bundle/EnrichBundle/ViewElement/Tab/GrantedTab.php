<?php

namespace Pim\Bundle\EnrichBundle\ViewElement\Tab;

use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * Render the given tab only if the user is granted to see it
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 *
 * TODO: DELETE IT
 */
class GrantedTab implements TabInterface
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var TabInterface */
    protected $tab;

    /** @var string */
    protected $acl;

    /**
     * @param SecurityFacade $securityFacade
     * @param TabInterface   $tab
     * @param string         $acl
     */
    public function __construct(
        SecurityFacade $securityFacade,
        TabInterface $tab,
        $acl
    ) {
        $this->securityFacade = $securityFacade;
        $this->tab            = $tab;
        $this->acl            = $acl;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(array $context = [])
    {
        return $this->tab->getContent($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(array $context = [])
    {
        return $this->tab->getTitle($context);
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible(array $context = [])
    {
        return $this->securityFacade->isGranted($this->acl) && $this->tab->isVisible($context);
    }

    /**
     * Add a new visibility checker to the object
     *
     * @param TabVisibilityCheckerInterface $checker
     *
     * @return TabInterface
     */
    public function addVisibilityChecker(TabVisibilityCheckerInterface $checker)
    {
        // TODO: Implement addVisibilityChecker() method.
    }

    /**
     * Set the visibility checkers to the object
     *
     * @param array $checkers
     *
     * @return TabInterface
     */
    public function setVisibilityCheckers(array $checkers)
    {
        // TODO: Implement setVisibilityCheckers() method.
    }


}
