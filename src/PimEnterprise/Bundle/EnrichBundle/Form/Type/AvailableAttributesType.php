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

use Pim\Bundle\EnrichBundle\Form\Type\AvailableAttributesType as BaseAvailableAttributesType;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Override available attributes type to remove attributes where permissions are revoked
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class AvailableAttributesType extends BaseAvailableAttributesType
{
    /** @var AttributeGroupAccessRepository */
    protected $attGroupAccessRepo;

    /**
     * Construct
     *
     * @param AttributeRepositoryInterface   $attributeRepository
     * @param UserContext                    $userContext
     * @param TranslatorInterface            $translator
     * @param AttributeGroupAccessRepository $attGroupAccessRepo
     * @param string                         $attributeClass
     * @param string                         $dataClass
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        UserContext $userContext,
        TranslatorInterface $translator,
        AttributeGroupAccessRepository $attGroupAccessRepo,
        $attributeClass,
        $dataClass
    ) {
        parent::__construct(
            $attributeRepository,
            $userContext,
            $translator,
            $attributeClass,
            $dataClass
        );

        $this->attGroupAccessRepo = $attGroupAccessRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'attributes',
            'light_entity',
            [
                'repository'         => $this->attributeRepository,
                'repository_options' => [
                    'excluded_attribute_ids' => $options['excluded_attributes'],
                    'locale_code'            => $this->userContext->getCurrentLocaleCode(),
                    'default_group_label'    => $this->translator->trans(
                        'Other',
                        [],
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
