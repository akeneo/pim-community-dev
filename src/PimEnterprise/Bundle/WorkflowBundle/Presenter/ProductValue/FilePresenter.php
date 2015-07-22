<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter\ProductValue;

use Pim\Bundle\CatalogBundle\Model\AbstractProductMedia;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\TwigAwareInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\TwigAware;

/**
 * Present a file value
 *
 * @author    Filips Alpe <filips@akeneo.com>
 */
class FilePresenter implements ProductValuePresenterInterface, TwigAwareInterface
{
    use TwigAware;

    /** @staticvar string */
    const TEMPLATE = 'PimEnterpriseWorkflowBundle:ProductValue:file.html.twig';

    /**
     * {@inheritdoc}
     */
    public function supports(ProductValueInterface $value)
    {
        return 'pim_catalog_file' === $value->getAttribute()->getAttributeType()
            && $value->getData() instanceof AbstractProductMedia;
    }

    /**
     * {@inheritdoc}
     */
    public function present(ProductValueInterface $value)
    {
        $filename = $value->getData()->getFilename();
        $title = $value->getData()->getOriginalFilename();

        if (null !== $filename && null !== $title) {
            return $this->twig->loadTemplate(static::TEMPLATE)->render(
                [
                    'filename' => $filename,
                    'title'    => $title
                ]
            );
        }
    }
}
