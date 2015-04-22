<?php

namespace Pim\Bundle\ReferenceDataBundle\RequirementChecker;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Checks if the custom ProductValue has the required relationships.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProductValueRelationshipChecker implements CheckerInterface
{
    /** @var string */
    protected $productValueClass;

    /** @var ObjectManager */
    protected $om;

    /** @var string */
    protected $failure;

    /**
     * @param ObjectManager $om
     * @param string        $productValueClass
     */
    public function __construct(ObjectManager $om, $productValueClass)
    {
        $this->om                = $om;
        $this->productValueClass = $productValueClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return sprintf(
            'Relation between the "%s" and the Reference Data must be configured.',
            $this->productValueClass
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFailure()
    {
        return $this->failure;
    }

    /**
     * {@inheritdoc}
     */
    public function isBlockingOnFailure()
    {
        return false;
    }
}
