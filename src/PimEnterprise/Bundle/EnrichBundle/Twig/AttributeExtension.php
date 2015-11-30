<?php

/*
* This file is part of the Akeneo PIM Enterprise Edition.
*
* (c) 2015 Akeneo SAS (http://www.akeneo.com)
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace PimEnterprise\Bundle\EnrichBundle\Twig;

use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Bundle\EnrichBundle\Twig\AttributeExtension as BaseAttributeExtension;

/**
 * Override Twig extension to allow to add Enterprise icons (as AssetCollectionType)
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AttributeExtension extends BaseAttributeExtension
{
    /**
     * @param AttributeRepositoryInterface $repository
     * @param array                        $communityIcons
     * @param array                        $eeIcons
     */
    public function __construct(AttributeRepositoryInterface $repository, array $communityIcons, array $eeIcons)
    {
        $this->repository = $repository;

        parent::__construct(array_merge($communityIcons, $eeIcons));
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array_merge(parent::getFunctions(), [
            'is_attribute_localizable' => new \Twig_Function_Method($this, 'isAttributeLocalizable'),
        ]);
    }

    /**
     * @param array $communityIcons
     * @param array $eeIcons
     *
     * @throws \LogicException
     *
     * @return bool
     */
    public function isAttributeLocalizable($code)
    {
        $attribute = $this->repository->findOneByIdentifier($code);

        if (null === $attribute) {
            throw new \LogicException(sprintf('Unable to find attribute "%s"', $code));
        }

        return $attribute->isLocalizable();
    }
}
