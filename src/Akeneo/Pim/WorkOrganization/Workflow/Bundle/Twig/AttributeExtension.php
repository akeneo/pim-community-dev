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

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AttributeExtension extends AbstractExtension
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $repository;

    public function __construct(IdentifiableObjectRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'get_attribute_label_from_code',
                [$this, 'getAttributeLabelFromCode'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction('is_attribute_localizable', [$this, 'isAttributeLocalizable']),
        ];
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public function getAttributeLabelFromCode($code)
    {
        if (null !== $attribute = $this->repository->findOneByIdentifier($code)) {
            return (string) $attribute;
        }

        return $code;
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
