<?php

namespace Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Checks if a view element is visible according to a voter.
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VoterVisibilityChecker implements VisibilityCheckerInterface
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
        if (!isset($config['attribute'])) {
            throw new \InvalidArgumentException('The "attribute" should be provided in the configuration.');
        }
        if (!isset($config['object'])) {
            throw new \InvalidArgumentException('The "object" should be provided in the configuration.');
        }
        $object = $this->getObject($config['object'], $context);

        return $this->securityFacade->isGranted(constant($config['attribute']), $object);
    }

    /**
     * If a string is provided as the object, extracts the object from the context,
     * otherwise returns the original object
     *
     * @param mixed $object
     * @param array $context
     *
     * @return mixed
     */
    protected function getObject($object, array $context)
    {
        if (is_string($object)) {
            $accessor = PropertyAccess::createPropertyAccessor();
            $object = $accessor->getValue($context, $object);
        }

        return $object;
    }
}
