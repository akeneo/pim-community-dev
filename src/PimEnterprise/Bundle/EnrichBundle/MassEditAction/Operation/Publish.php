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

use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\AbstractMassEditAction;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Batch operation to publish products
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Publish extends AbstractMassEditAction
{
    /** @var PublishedProductManager */
    protected $manager;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var string */
    protected $rootDir;

    /** @var string */
    protected $environment;

    /**
     * @param PublishedProductManager  $manager
     * @param SecurityContextInterface $securityContext
     * @param string                   $rootDir
     * @param string                   $environment
     */
    public function __construct(
        PublishedProductManager $manager,
        SecurityContextInterface $securityContext,
        $rootDir,
        $environment
    ) {
        $this->manager         = $manager;
        $this->securityContext = $securityContext;
        $this->rootDir         = $rootDir;
        $this->environment     = $environment;
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
        $pathFinder  = new PhpExecutableFinder();
        $identifiers = [];
        foreach ($this->objects as $key => $product) {
            if (!$product instanceof ProductInterface) {
                throw new \LogicException(
                    sprintf(
                        'Cannot perform mass edit action "%s" on object of type "%s", ' .
                        'expecting "Pim\Bundle\CatalogBundle\Model\ProductInterface"',
                        __CLASS__,
                        ClassUtils::getClass($product)
                    )
                );
            }

            if ($this->securityContext->isGranted(Attributes::OWN, $product)) {
                $identifier = $product->getIdentifier();
                $identifiers[] = sprintf('"%s"', $identifier) ;
            }
        }

        $logFile = sprintf('%s/logs/mass_action.log', $this->rootDir);
        if (file_exists($logFile)) {
            unlink($logFile);
        }

        $cmd = sprintf(
            '%s %s/console pim:product:mass-publish "%s" --env=%s >> %s 2>&1 &',
            $pathFinder->find(),
            $this->rootDir,
            json_encode($identifiers),
            $this->environment,
            $logFile
        );

        exec($cmd);
    }
}
