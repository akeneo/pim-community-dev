<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ReferenceDataBundle\Workflow\Presenter;

/**
 * Present changes on a collection of reference data
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ReferenceDataCollectionPresenter extends AbstractReferenceDataPresenter
{
    /**
     * {@inheritdoc}
     */
    public function supportsChange($attributeType)
    {
        return 'pim_reference_data_multiselect' === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        $result = [];

        if (null === $data) {
            return $result;
        }

        foreach ($data as $reference) {
            $result[] = (string) $reference;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        $result = [];
        $repository = $this->repositoryResolver->resolve($this->referenceDataName);
        $references = $repository->findBy(['code' => $change['data']]);

        foreach ($references as $reference) {
            $result[] = (string) $reference;
        }

        return $result;
    }
}
