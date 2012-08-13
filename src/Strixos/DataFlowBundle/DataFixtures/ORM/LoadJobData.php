<?php
// src/Strixos/CatalogBundle/DataFixtures/ORM/LoadAttributeSetData.php

namespace Strixos\CatalogBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Strixos\DataFlowBundle\Entity\Job;
use Strixos\DataFlowBundle\Entity\Step;
use Strixos\DataFlowBundle\Model\Extract\CsvFileReader;


/**
 * Execute with "php app/console doctrine:fixtures:load"
 *
 * @author Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadJobData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /*
        // add job
        $job = new Job();
        $job->setCode('Import Magento Product');

        // add steps
        $stepRead = new Step();
        $stepRead->setCode('Read product file');
        $stepRead->setBehaviour('Strixos\DataFlowBundle\Model\Extract\CsvFileReader');

        // TODO deal with options as json array ?
        $filename = '~export-admin-all-configurable.csv';
        $stepRead->setOptions($filename)
        $job->addOrderedStep($stepRead);
        $manager->persist($stepRead);

        // TODO: transform, insert

        // flush data
        $manager->persist($job);
        $manager->flush();
        */
    }

    /**
     * Executing order
     * @see Doctrine\Common\DataFixtures.OrderedFixtureInterface::getOrder()
     */
    public function getOrder()
    {
        return 10;
    }
}