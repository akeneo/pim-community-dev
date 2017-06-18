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

use Pim\Bundle\EnrichBundle\Twig\AttributeExtension as BaseAttributeExtension;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

/**
 * Override Twig extension to add 'isAttributeLocalizable' method
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AttributeExtension extends BaseAttributeExtension
{
    /**
     * @param AttributeRepositoryInterface $repository
     */
    public function __construct(AttributeRepositoryInterface $repository)
    {
        $this->repository = $repository;

        parent::__construct($repository);
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array_merge(parent::getFunctions(), [
            new \Twig_SimpleFunction('is_attribute_localizable', [$this, 'isAttributeLocalizable']),
        ]);
    }

    /**
     * @param string $code
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
