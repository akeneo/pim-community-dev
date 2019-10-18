<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\ReferenceData;

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
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return 'pim_reference_data_multiselect' === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        if (null === $data) {
            return [];
        }

        return $data;
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
