<?php

namespace Pim\Bundle\EnrichBundle\ViewElement\Tab;

use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * Checks if a tab is visible according to a voter.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TabVisibilityCheckerVoter implements TabVisibilityCheckerInterface
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var array */
    protected $context;

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
    public function setContext(array $context = [])
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible(array $context = [])
    {
        $this->context = array_merge($this->context, $context);

        if (!isset($this->context['attribute'])) {
            throw new \InvalidArgumentException('The "attribute" should be provided in the context.');
        }
        if (!isset($this->context['entity'])) {
            throw new \InvalidArgumentException('The "entity" should be provided in the context.');
        }

        return $this->securityFacade->isGranted(
            constant($this->context['attribute']),
            $this->context['entity']
        );
    }
}
