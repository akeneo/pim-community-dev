<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Asset type
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AssetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('code', 'text');
        $builder->add('description', 'textarea');
//        $builder->add('tags', new FormTypeSelect2Extension());
        $builder
            ->add(
                'tags',
                'entity',
                [
                    'class' => 'PimEnterprise\Component\ProductAsset\Model\Tag',
                    'query_builder' => function (EntityRepository $repository) {
                        return $repository->createQueryBuilder('tag')
                            ->select('tag')
                            ->addOrderBy('tag.code', 'ASC');
                    },
                    'multiple' => true,
                    'expanded' => false,
                    'select2'  => true
                ]
            );
        $builder->add('endOfUseAt', 'date');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pimee_product_asset';
    }
}
