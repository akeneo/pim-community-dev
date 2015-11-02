<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Twig;

use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Twig extension to know if a user is granted the given role on the given attribute or locale code
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class IsGrantedExtension extends \Twig_Extension
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param AttributeRepositoryInterface  $attributeRepository
     * @param LocaleRepositoryInterface     $localeRepository
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->attributeRepository  = $attributeRepository;
        $this->localeRepository     = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pimee_attribute_granted_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'is_attribute_granted',
                [$this, 'isAttributeGranted']
            ),
            new \Twig_SimpleFunction(
                'is_locale_granted',
                [$this, 'isLocaleGranted']
            ),
        ];
    }

    /**
     * @param string      $role
     * @param string      $attributeCode
     * @param string|null $field
     *
     * @throws \LogicException
     *
     * @return bool
     */
    public function isAttributeGranted($role, $attributeCode, $field = null)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

        if (null === $attribute) {
            throw new \LogicException(sprintf('Attribute "%s" not found', $attributeCode));
        }

        $attributeGroup = $attribute->getGroup();

        if (null !== $field) {
            return $this->authorizationChecker->isGranted($role, new FieldVote($attributeGroup, $field));
        }

        return $this->authorizationChecker->isGranted($role, $attributeGroup);
    }

    /**
     * @param string      $role
     * @param string      $localeCode
     * @param string|null $field
     *
     * @throws \LogicException
     *
     * @return bool
     */
    public function isLocaleGranted($role, $localeCode, $field = null)
    {
        $locale = $this->localeRepository->findOneByIdentifier($localeCode);

        if (null === $locale) {
            throw new \LogicException(sprintf('Locale "%s" not found', $localeCode));
        }

        if (null !== $field) {
            return $this->authorizationChecker->isGranted($role, new FieldVote($locale, $field));
        }

        return $this->authorizationChecker->isGranted($role, $locale);
    }
}
