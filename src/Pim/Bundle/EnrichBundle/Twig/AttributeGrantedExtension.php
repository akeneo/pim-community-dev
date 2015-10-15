<?php

namespace Pim\Bundle\EnrichBundle\Twig;

use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Twig extension to know if a user is granted the given role on the given attribute code
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGrantedExtension extends \Twig_Extension
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param AttributeRepositoryInterface  $attributeRepository
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->attributeRepository  = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_attribute_granted_extension';
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
        ];
    }

    /**
     * @param string      $role
     * @param string      $attributeCode
     * @param string|null $field
     *
     * @return bool
     */
    public function isAttributeGranted($role, $attributeCode, $field = null)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

        if (null === $attribute) {
            return false;
        }

        $attributeGroup = $attribute->getGroup();

        if (null !== $field) {
            return $this->authorizationChecker->isGranted($role, new FieldVote($attributeGroup, $field));
        }

        return $this->authorizationChecker->isGranted($role, $attributeGroup);
    }
}
