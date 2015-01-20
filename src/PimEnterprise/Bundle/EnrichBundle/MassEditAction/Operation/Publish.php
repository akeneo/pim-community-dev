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

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditAction;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Batch operation to publish products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 */
class Publish extends AbstractMassEditAction
{
    /**
     * @var PublishedProductManager
     */
    protected $manager;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @param PublishedProductManager  $manager
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(PublishedProductManager $manager, SecurityContextInterface $securityContext)
    {
        $this->manager         = $manager;
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pimee_enrich_mass_publish';
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
     * @return Publish
     */
    public function setNotGrantedIdentifiers($notGranted)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        foreach ($this->objects as $key => $object) {
            if (!$object instanceof ProductInterface) {
                throw new \LogicException(
                    sprintf(
                        'Cannot perform mass edit action "%s" on object of type "%s", '.
                        'expecting "Pim\Bundle\CatalogBundle\Model\ProductInterface"',
                        __CLASS__,
                        get_class($object)
                    )
                );
            }

            try {
                $this->doPerform($object);
            } catch (\RuntimeException $e) {
                unset($this->objects[$key]);
            }
        }

        // TODO : about refactoring of this one,
        // we should provide a BulkPublishInterface and provide a BulkProductPublisher implementation which publish
        // all products data and then, publish all associations where products appears in owner or owned side, it could
        // make the code far more readable and decouple this logic from the mass edit operation

        $this->manager->publishAssociations($this->objects);
    }

    /**
     * {@inheritdoc}
     */
    protected function doPerform(ProductInterface $product)
    {
        if (!$this->securityContext->isGranted(Attributes::OWN, $product)) {
            throw new \RuntimeException(
                sprintf(
                    'Cannot publish product "%s" because current user does not own it',
                    (string) $product
                )
            );
        }

        $this->manager->publish($product, ['with_associations' => false]);
    }
}
