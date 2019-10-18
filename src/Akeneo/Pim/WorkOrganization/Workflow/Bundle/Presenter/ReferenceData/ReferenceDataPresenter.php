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
 * Present changes on reference data
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ReferenceDataPresenter extends AbstractReferenceDataPresenter
{
    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return 'pim_reference_data_simpleselect' === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        $repository = $this->repositoryResolver->resolve($this->referenceDataName);

        return (string) $repository->findOneBy(['code' => $change['data']]);
    }
}
