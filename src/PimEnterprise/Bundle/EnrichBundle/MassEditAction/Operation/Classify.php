<?php

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify as BaseClassify;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Batch operation to classify products
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class Classify extends BaseClassify
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @param CategoryManager          $categoryManager
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(CategoryManager $categoryManager, SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
        $this->categoryManager = $categoryManager;
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
     * Override to bypass the creation of a product draft
     *
     * @return array
     */
    public function getSavingOptions()
    {
        $options = parent::getSavingOptions();
        $options['bypass_product_draft'] = true;

        return $options;
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
