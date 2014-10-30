<?php

namespace Pim\Bundle\EnrichBundle\ViewElement;

use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Basic protected view element
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseProtectedViewElement extends BaseViewElement implements ProtectedViewElementInterface
{
    /** @staticvar string */
    const CONTEXT_KEY = '__context';

    /** @var array */
    protected $aclResources;

    /** @var ViewElementVisibilityCheckerInterface[] */
    protected $visibilityCheckers = [];

    /**
     * @param string $alias
     * @param string $template
     * @param array  $aclResources
     */
    public function __construct($alias, $template, array $aclResources = [])
    {
        parent::__construct($alias, $template);

        $this->aclResources = $aclResources;
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible(array $context = [])
    {
        foreach ($this->visibilityCheckers as $checker) {
            if (false === $checker->isVisible($context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getAclResources(array $context = [])
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $result = [];

        foreach ($this->aclResources as $aclResource) {
            if (is_array($aclResource)) {
                foreach ($aclResource as &$parameter) {
                    if (is_string($parameter) && 0 === strpos($parameter, static::CONTEXT_KEY)) {
                        $parameter = $accessor->getValue($context, substr($parameter, strlen(static::CONTEXT_KEY)));
                    }
                }
            }
            $result[] = $aclResource;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function addVisibilityChecker(ViewElementVisibilityCheckerInterface $checker)
    {
        $this->visibilityCheckers[] = $checker;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibilityCheckers(array $checkers)
    {
        $this->visibilityCheckers = $checkers;

        return $this;
    }
}
