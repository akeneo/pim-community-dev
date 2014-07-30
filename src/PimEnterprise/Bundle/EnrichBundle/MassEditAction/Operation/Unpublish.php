<?php

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Batch operation to unpublish products
 *
 * @author    Julien Janvier <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class Unpublish extends PublishedProductMassEditOperation
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
        return 'pimee_enrich_mass_unpublish';
    }

    /**
     * The list of not granted product identifiers
     *
     * @return string
     */
    public function getNotGrantedIdentifiers()
    {
        /** @var PublishedProductInterface[] $publisheds */
        $publisheds   = $this->getObjectsToMassEdit();
        $notGranted = [];
        foreach ($publisheds as $published) {
            if ($this->securityContext->isGranted(Attributes::OWN, $published->getOriginalProduct()) === false) {
                $notGranted[] = (string) $published->getIdentifier();
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
    protected function doPerform(PublishedProductInterface $published)
    {
        if ($this->securityContext->isGranted(Attributes::OWN, $published->getOriginalProduct())) {
            $this->manager->unpublish($published);
        }
    }
}
