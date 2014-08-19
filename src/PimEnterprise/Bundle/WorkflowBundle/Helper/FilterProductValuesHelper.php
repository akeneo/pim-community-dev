<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Helper;

use Symfony\Component\Security\Core\SecurityContextInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Helper for filtering product values
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class FilterProductValuesHelper
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * Filter the provided values
     * Returns values that the current user is allowed to see
     * If locale is specified, only values in this locale are returned
     *
     * @param \Pim\Bundle\CatalogBundle\Model\AbstractProductValue[] $values
     * @param string|null                                            $locale
     *
     * @return \Pim\Bundle\CatalogBundle\Model\AbstractProductValue[]
     */
    public function filter(array $values, $locale = null)
    {
        $filteredValues = [];

        foreach ($values as $value) {
            $attribute = $value->getAttribute();

            if (null !== $locale && $attribute->isLocalizable() && $value->getLocale() !== $locale) {
                continue;
            }

            if (true === $this->securityContext->isGranted(Attributes::VIEW_ATTRIBUTES, $attribute->getGroup())) {
                $filteredValues[] = $value;
            }
        }

        return $filteredValues;
    }
}
