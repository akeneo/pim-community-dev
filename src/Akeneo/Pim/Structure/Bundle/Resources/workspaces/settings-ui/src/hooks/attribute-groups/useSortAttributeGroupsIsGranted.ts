import {useEffect, useState} from 'react';
import {useSecurity} from '@akeneo-pim-community/legacy-bridge';

const useSortAttributeGroupsIsGranted = (): boolean => {
    const [sortIsGranted, setSortIsGranted] = useState<boolean>(false);
    const {isGranted} = useSecurity();

    useEffect(() => {
        if (typeof isGranted === 'function') {
            setSortIsGranted(
                isGranted('pim_enrich_attributegroup_sort')
            );
        }
    }, [isGranted])

    return sortIsGranted;
}

export {useSortAttributeGroupsIsGranted};