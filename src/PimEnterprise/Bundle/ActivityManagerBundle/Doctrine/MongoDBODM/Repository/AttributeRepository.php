<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\MongoDBODM\Repository;

use Doctrine\ODM\MongoDB\DocumentManager;
use PimEnterprise\Component\ActivityManager\Presenter\PresenterInterface;
use PimEnterprise\Component\ActivityManager\Repository\StructuredAttributeRepositoryInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AttributeRepository implements StructuredAttributeRepositoryInterface
{
    /** @var PresenterInterface */
    protected $attributePresenter;

    /** @var DocumentManager */
    protected $documentManager;

    /** @var string */
    protected $productClass;

    /**
     * @param DocumentManager    $documentManager
     * @param PresenterInterface $attributePresenter
     * @param string             $productClass
     */
    public function __construct(
        DocumentManager $documentManager,
        PresenterInterface $attributePresenter,
        $productClass
    ) {
        $this->attributePresenter = $attributePresenter;
        $this->documentManager = $documentManager;
        $this->productClass = $productClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getStructuredAttributes($productId, $scope, $locale)
    {
        $productsQueryBuilder = $this->documentManager->createQueryBuilder($this->productClass);

        $product = $productsQueryBuilder->select('family', 'normalizedData')
            ->hydrate(false)
            ->field('_id')->equals($productId)
            ->getQuery()
            ->execute();

        return $this->attributePresenter->present($product);
    }
}
