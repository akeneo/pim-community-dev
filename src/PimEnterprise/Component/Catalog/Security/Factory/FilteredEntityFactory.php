<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Catalog\Security\Factory;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use PimEnterprise\Component\Security\NotGrantedDataFilterInterface;

/**
 * Create a filtered entity (meaning with only granted data) from the entity.
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class FilteredEntityFactory
{
    /** @var NotGrantedDataFilterInterface[] */
    private $notGrantedDataFilters;

    /**
     * @param NotGrantedDataFilterInterface[] $notGrantedDataFilters
     */
    public function __construct(array $notGrantedDataFilters)
    {
        $this->notGrantedDataFilters = $notGrantedDataFilters;
    }

    /**
     * @param mixed $fullEntity
     *
     * @throws InvalidObjectException
     *
     * @return mixed
     */
    public function create($fullEntity)
    {
        $filteredEntity = clone $fullEntity;

        foreach ($this->notGrantedDataFilters as $notGrantedDataFilter) {
            if (!$notGrantedDataFilter instanceof NotGrantedDataFilterInterface) {
                throw InvalidObjectException::objectExpected(ClassUtils::getClass($notGrantedDataFilter), NotGrantedDataFilterInterface::class);
            }

            $filteredEntity = $notGrantedDataFilter->filter($filteredEntity);
        }

        return $filteredEntity;
    }
}
