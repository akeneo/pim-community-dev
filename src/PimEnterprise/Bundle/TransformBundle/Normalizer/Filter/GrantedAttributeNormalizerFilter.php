<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TransformBundle\Normalizer\Filter;

use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Bundle\TransformBundle\Normalizer\Filter\NormalizerFilterInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Filter the granted attribute objects.
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class GrantedAttributeNormalizerFilter implements NormalizerFilterInterface
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
     * {@inheritdoc}
     */
    public function filter(Collection $objects, array $context = [])
    {
        $objects = $objects->filter(
            function ($attribute) {
                if (!$attribute instanceof AttributeInterface) {
                    throw new \Exception('This filter only handles objects of type "AttributeInterface"');
                }
                $attributeGroup = $attribute->getGroup();

                return $this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeGroup);
            }
        );

        return $objects;
    }
}
