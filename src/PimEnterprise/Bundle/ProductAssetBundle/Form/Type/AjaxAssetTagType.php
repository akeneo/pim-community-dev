<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Form\Type;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\UIBundle\Form\Transformer\AjaxCreatableEntityTransformerFactory;
use Pim\Bundle\UIBundle\Form\Type\AjaxEntityType;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * Ajax asset tag type
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AjaxAssetTagType extends AjaxEntityType
{
    /** @var SaverInterface */
    protected $saver;

    /** @var string */
    protected $transformerClass;

    /**
     * @param AjaxCreatableEntityTransformerFactory $transformerFactory
     * @param SaverInterface                        $saver
     * @param RouterInterface                       $router
     * @param UserContext                           $userContext
     * @param string                                $transformerClass
     */
    public function __construct(
        AjaxCreatableEntityTransformerFactory $transformerFactory,
        SaverInterface $saver,
        RouterInterface $router,
        UserContext $userContext,
        $transformerClass
    ) {
        $this->transformerFactory = $transformerFactory;
        $this->saver              = $saver;
        $this->router             = $router;
        $this->userContext        = $userContext;
        $this->transformerClass   = $transformerClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_ajax_asset_tag';
    }

    /**
     * {@inheritdoc}
     */
    protected function getTransformer(array $options)
    {
        return $this->transformerFactory->create(
            $this->saver,
            $this->getTransformerOptions($options),
            $this->transformerClass
        );
    }
}
