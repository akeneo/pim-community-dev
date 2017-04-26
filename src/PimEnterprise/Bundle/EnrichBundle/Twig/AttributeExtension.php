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

/**
 * Twig extension to test if an attribute is 'localizable'
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AttributeExtension extends \Twig_Extension
{
    /**
     * @param AttributeRepositoryInterface $repository
     */
    public function __construct(AttributeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'is_attribute_localizable' => new \Twig_Function_Method($this, 'isAttributeLocalizable')
        ];
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
