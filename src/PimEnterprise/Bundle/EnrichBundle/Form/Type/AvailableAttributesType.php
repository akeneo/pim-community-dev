<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Pim\Bundle\EnrichBundle\Form\Type\AvailableAttributesType as PimAvailableAttributesType;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Voter\AttributeGroupVoter;

/**
 * Override available attributes type to remove attributes where rights are revoked
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AvailableAttributesType extends PimAvailableAttributesType
{
    /** @var AttributeGroupAccessRepository */
    protected $attGroupAccessRepo;

    /**
     * Construct
     *
     * @param string                         $attributeClass
     * @param AttributeRepository            $attributeRepository
     * @param UserContext                    $userContext
     * @param TranslatorInterface            $translator
     * @param AttributeGroupAccessRepository $attGroupAccessRepo
     */
    public function __construct(
        $attributeClass,
        AttributeRepository $attributeRepository,
        UserContext $userContext,
        TranslatorInterface $translator,
        AttributeGroupAccessRepository $attGroupAccessRepo
    ) {
        parent::__construct($attributeClass, $attributeRepository, $userContext, $translator);

        $this->attGroupAccessRepo = $attGroupAccessRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $revokedAttributeIds = $this->attGroupAccessRepo->getRevokedAttributeIds(
            $this->userContext->getUser(),
            AttributeGroupVoter::EDIT_ATTRIBUTES
        );

        $builder->add(
            'attributes',
            'light_entity',
            [
                'repository' => $this->attributeRepository,
                'repository_options' => [
                    'excluded_attribute_ids' => array_unique(array_merge($options['attributes'], $revokedAttributeIds)),
                    'locale_code'            => $this->userContext->getCurrentLocaleCode(),
                    'default_group_label'    => $this->translator->trans(
                        'Other',
                        array(),
                        null,
                        $this->userContext->getCurrentLocaleCode()
                    ),
                ],
                'multiple' => true,
                'expanded' => false,
            ]
        );
    }
}
