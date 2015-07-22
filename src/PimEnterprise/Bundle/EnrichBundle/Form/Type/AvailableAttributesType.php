<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Pim\Bundle\EnrichBundle\Form\Type\AvailableAttributesType as BaseAvailableAttributesType;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Override available attributes type to remove attributes where permissions are revoked
 *
 * @author    Romain Monceau <romain@akeneo.com>
 */
class AvailableAttributesType extends BaseAvailableAttributesType
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
            Attributes::VIEW_ATTRIBUTES
        );

        $builder->add(
            'attributes',
            'light_entity',
            [
                'repository'         => $this->attributeRepository,
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
