jest.unmock('./usePartitionAttributesByGroup');

import {renderHook} from '@testing-library/react-hooks';
import {usePartitionAttributesByGroup} from './usePartitionAttributesByGroup';

test('it partitions attributes with factories into buckets of attribute groups', () => {
    const nameAttribute = {
        id: 'name',
        label: '[name]',
        group_code: 'marketing',
        group_label: '[marketing]',
        factory: () => null,
    };
    const descriptionAttribute = {
        id: 'description',
        label: '[description]',
        group_code: 'marketing',
        group_label: '[marketing]',
        factory: () => null,
    };
    const skuAttribute = {
        id: 'sku',
        label: '[sku]',
        group_code: 'technical',
        group_label: 'Technical',
        factory: () => null,
    };
    const eanAttribute = {
        id: 'ean',
        label: '[ean]',
        group_code: 'technical2',
        group_label: 'Technical', // group with same label as technical
        factory: () => null,
    };
    const sizeAttribute = {
        id: 'size',
        label: '[size]',
        group_code: 'technical',
        group_label: '[technical]',
        factory: () => null,
    };
    const publisherAttribute = {
        id: 'publisher',
        label: '[publisher]',
        group_code: 'other',
        group_label: '[other]',
        factory: () => null,
    };

    const attributesWithFactories = [
        nameAttribute,
        descriptionAttribute,
        skuAttribute,
        eanAttribute,
        sizeAttribute,
        publisherAttribute,
    ];

    const {result} = renderHook(() => usePartitionAttributesByGroup(attributesWithFactories));

    expect(result.current).toEqual([
        {
            code: 'marketing',
            label: '[marketing]',
            attributesWithFactories: [nameAttribute, descriptionAttribute],
        },
        {
            code: 'technical',
            label: 'Technical',
            attributesWithFactories: [skuAttribute, sizeAttribute],
        },
        {
            code: 'technical2',
            label: 'Technical',
            attributesWithFactories: [eanAttribute],
        },
        {
            code: 'other',
            label: '[other]',
            attributesWithFactories: [publisherAttribute],
        },
    ]);
});

test('it returns an empty list on undefined', () => {
    const {result} = renderHook(() => usePartitionAttributesByGroup(undefined));

    expect(result.current).toEqual([]);
});
