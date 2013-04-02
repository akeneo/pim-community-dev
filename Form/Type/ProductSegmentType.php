<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Doctrine\ORM\EntityRepository;

use Sonata\AdminBundle\Form\FormMapper;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Type for product segment form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductSegmentType extends AbstractType
{

    const MODE_NODE = 'node';
    const MODE_TREE = 'tree';

    protected $mode;

    /**
     * Constructor
     *
     * @param string $mode
     */
    public function __construct($mode = self::MODE_NODE)
    {
        $this->mode = $mode;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('title');

        $builder->add('code');

        if ($this->mode === self::MODE_NODE) {
            $builder->add('isDynamic', 'checkbox', array('required' => false));

            $builder->add(
                'parent',
                'entity',
                array(
                    'class' => 'Pim\Bundle\ProductBundle\Entity\ProductSegment',
                    'property' => 'code',
                    'query_builder' => function (EntityRepository $repository) {
                        return $repository->createQueryBuilder('ps')->orderBy('ps.left');
                    }
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\ProductBundle\Entity\ProductSegment'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_segment';
    }
}
