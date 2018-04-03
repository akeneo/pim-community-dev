<?php

namespace Pim\Bundle\ReferenceDataBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryResolverInterface;

/**
 * Transforms reference data codes to IDs.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataIdResolver
{
    /** @var ReferenceDataRepositoryResolverInterface */
    protected $repositoryResolver;

    /**
     * @param ReferenceDataRepositoryResolverInterface $repositoryResolver
     */
    public function __construct(ReferenceDataRepositoryResolverInterface $repositoryResolver)
    {
        $this->repositoryResolver = $repositoryResolver;
    }

    /**
     * Resolve reference data IDs from codes
     *
     * @param string $referenceData reference data name
     * @param mixed  $codes         a code or an array of code
     *
     * @return mixed, a reference data ID or an array of reference data IDs
     */
    public function resolve($referenceData, $codes)
    {
        $repository = $this->repositoryResolver->resolve($referenceData);

        if (is_array($codes)) {
            $ids = [];
            foreach ($codes as $code) {
                $ids[] = $this->resolveOne($repository, $referenceData, $code);
            }

            return $ids;
        }

        return $this->resolveOne($repository, $referenceData, $codes);
    }

    /**
     * @param ObjectRepository $repository
     * @param string           $referenceDataName
     * @param string           $code
     *
     * @throws \LogicException
     *
     * @return int
     */
    protected function resolveOne(ObjectRepository $repository, $referenceDataName, $code)
    {
        //TODO: do not hydrate them, use a scalar result
        $referenceData = $repository->findOneBy(['code' => $code]);

        if (null === $referenceData) {
            throw new \LogicException(
                sprintf('No reference data "%s" with code "%s" has been found', $referenceDataName, $code)
            );
        }

        return $referenceData->getId();
    }
}
