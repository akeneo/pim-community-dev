<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify as BaseClassify;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Batch operation to classify products
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class Classify extends BaseClassify
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * @param CategoryManager          $categoryManager
     * @param BulkSaverInterface       $productSaver
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        CategoryManager $categoryManager,
        BulkSaverInterface $productSaver,
        SecurityContextInterface $securityContext
    ) {
        parent::__construct($categoryManager, $productSaver);
        $this->securityContext = $securityContext;
        $this->trees           = $categoryManager->getAccessibleTrees($securityContext->getToken()->getUser());
        $this->categories      = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pimee_enrich_mass_classify';
    }

    /**
     * The list of not granted product identifiers
     *
     * @return string
     */
    public function getNotGrantedIdentifiers()
    {
        $products   = $this->getObjectsToMassEdit();
        $notGranted = [];
        foreach ($products as $product) {
            if ($this->securityContext->isGranted(Attributes::OWN, $product) === false) {
                $notGranted[] = (string) $product->getIdentifier();
            }
        }

        return implode(', ', $notGranted);
    }

    /**
     * Allows to set the form but we don't use not granted data from it
     *
     * @param string $notGranted
     *
     * @return Classify
     */
    public function setNotGrantedIdentifiers($notGranted)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function doPerform(ProductInterface $product)
    {
        if (!$this->securityContext->isGranted(Attributes::OWN, $product)) {
            throw new \RuntimeException(
                sprintf(
                    'Cannot classify product "%s" because current user does not own it',
                    (string) $product
                )
            );
        }

        return parent::doPerform($product);
    }
}
