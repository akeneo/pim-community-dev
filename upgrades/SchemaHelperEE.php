<?php

namespace Pim\Upgrade;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Schema helper
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SchemaHelperEE extends SchemaHelper
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->classMapping['product_draft']           = 'pimee_workflow.model.product_draft.class';
        $this->classMapping['published_product']       = 'pimee_workflow.entity.published_product.class';
        $this->classMapping['published_product_media'] = 'pimee_workflow.entity.published_product_media.class';

        $this->productResources[] = 'published_product';
        $this->productResources[] = 'published_product_media';
        $this->productResources[] = 'product_draft';
    }
}
