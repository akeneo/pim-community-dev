<?php

namespace Pim\Bundle\InstallerBundle\Transformer\Property;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\DataFixtures\ReferenceRepository as ReferenceRepository2;
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
class FixtureReferenceTransformer implements PropertyTransformerInterface
{
    /**
     * @var ReferenceRepository2
     */
    protected $referenceRepository;

    /**
     * @var PropertyTransformerInterface
     */
    protected $entityTransformer;

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
    public function transform($value, array $options = array())
    {
        $value = trim($value);

        if (!$value) {
            return $options['multiple'] ? array() : null;
        }
        $getReference = function ($value) use ($options) {
            return $this->getReference($options['class'], $value);
        };

        return (isset($options['multiple']) && $options['multiple'])
            ? array_map($getReference, preg_split('/s*,\s*/', $value))
            : $getReference($value);
    }

    /**
     * Returns an object for a given class and code
     *
     * @param string $class
     * @param string $code
     *
     * @return object
     */
    protected function getReference($class, $code)
    {
        $refName = $class . '.' . $code;
        if ($this->referenceRepository && $this->referenceRepository->hasReference($refName)) {
            return $this->referenceRepository->getReference($refName);
        } else {
            return $this->entityTransformer->transform($code, array('class' => $class));
        }
    }
}
