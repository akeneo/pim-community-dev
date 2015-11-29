<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Helper;

use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Helper for filtering product values
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class FilterProductValuesHelper
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Filter the provided values
     * Returns values that the current user is allowed to see
     * If locale is specified, only values in this locale are returned
     *
     * @param \Pim\Component\Catalog\Model\ProductValueInterface[] $values
     * @param string|null                                             $locale
     *
     * @return \Pim\Component\Catalog\Model\ProductValueInterface[]
     */
    public function filter(array $values, $locale = null)
    {
        $filteredValues = [];

        foreach ($values as $value) {
            $attribute = $value->getAttribute();

            if (null !== $locale && $attribute->isLocalizable() && $value->getLocale() !== $locale) {
                continue;
            }

            if (true === $this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attribute->getGroup())) {
                $filteredValues[] = $value;
            }
        }

        return $filteredValues;
    }
}
