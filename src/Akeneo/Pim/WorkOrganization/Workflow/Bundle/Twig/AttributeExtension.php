<?php

/*
* This file is part of the Akeneo PIM Enterprise Edition.
*
* (c) 2015 Akeneo SAS (http://www.akeneo.com)
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Twig;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

/**
 * Override Twig extension to add 'isAttributeLocalizable' method
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AttributeExtension extends \Twig_Extension
{
    /** @var AttributeRepositoryInterface */
    private $repository;

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
        return array_merge([
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
