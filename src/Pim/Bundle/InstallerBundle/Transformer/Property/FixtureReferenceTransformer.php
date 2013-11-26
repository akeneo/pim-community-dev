<?php

namespace Pim\Bundle\InstallerBundle\Transformer\Property;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Pim\Bundle\ImportExportBundle\Transformer\Property\AbstractAssociationTransformer;
use Pim\Bundle\ImportExportBundle\Transformer\Property\AssociationTransformerInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface;

/**
 * Fixture Reference transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @author Antoine Guigan <aguigan@qimnet.com>
 */
class FixtureReferenceTransformer extends AbstractAssociationTransformer
{
    /**
     * @var AssociationTransformerInterface
     */
    protected $entityTransformer;

    /**
     * @var ReferenceRepository
     */
    protected $referenceRepository;

    /**
     * Constructor
     *
     * @param PropertyTransformerInterface $entityTransformer
     */
    public function __construct(PropertyTransformerInterface $entityTransformer)
    {
        $this->entityTransformer = $entityTransformer;
    }

    /**
     * Sets the reference repository
     *
     * @param ReferenceRepository $referenceRepository
     */
    public function setReferenceRepository(ReferenceRepository $referenceRepository = null)
    {
        $this->referenceRepository = $referenceRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference($class, $code)
    {
        $refName = $class . '.' . $code;
        if ($this->referenceRepository && $this->referenceRepository->hasReference($refName)) {
            return $this->referenceRepository->getReference($refName);
        } else {
            return $this->entityTransformer->getReference($class, $code);
        }
    }
}
