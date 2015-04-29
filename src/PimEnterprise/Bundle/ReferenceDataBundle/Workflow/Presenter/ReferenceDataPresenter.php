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
 * Present changes on reference data
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ReferenceDataPresenter extends AbstractReferenceDataPresenter
{
    /**
     * {@inheritdoc}
     */
    public function supports($data, array $change)
    {
        $supports = parent::supports($data, $change);
        if (true === $supports) {
            $this->referenceDataName = $data->getAttribute()->getReferenceDataName();

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsChange(array $change)
    {
        return 'pim_reference_data_simpleselect' === $this->attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        return (string) $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        $repository = $this->repositoryResolver->resolve($this->referenceDataName);

        return (string) $repository->findOneBy(['code' => $change['value']]);
    }
}
