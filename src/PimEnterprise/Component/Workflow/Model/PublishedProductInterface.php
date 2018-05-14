<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Model;

use Akeneo\Tool\Component\Versioning\Model\Version;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Published product interface
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface PublishedProductInterface extends ProductInterface
{
    /**
     * @return mixed
     */
    public function getOriginalProduct();

    /**
     * @param ProductInterface $product
     *
     * @return PublishedProductInterface
     */
    public function setOriginalProduct(ProductInterface $product);

    /**
     * @return Version
     */
    public function getVersion();

    /**
     * @param Version $version
     *
     * @return PublishedProductInterface
     */
    public function setVersion(Version $version);
}
