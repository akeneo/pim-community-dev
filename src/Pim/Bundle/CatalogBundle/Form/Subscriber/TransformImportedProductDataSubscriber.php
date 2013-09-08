<?php

namespace Pim\Bundle\CatalogBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Basic imported product data transformer
 * Intends to be overrided in 3rd party bundles
 *
 * @see Pim\Bundle\ImportExportBundle\Form\Subscriber\TransformImportedProductDataSubscriber
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformImportedProductDataSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array();
    }
}
