import {createTargetFromProductMappingSchema} from './createTargetFromProductMappingSchema';

jest.unmock('./createTargetFromProductMappingSchema');

test('it creates target from the product mapping schema', () => {
    const PRODUCT_MAPPING_SCHEMA = {
        properties: {
            uuid: {
                type: 'string',
            },
            colors: {
                type: 'array',
                items: {
                    type: 'string'
                },
                title: 'All colors',
                description: 'This is the list of the colors',
            },
        },
    };

    expect(
        createTargetFromProductMappingSchema('colors', PRODUCT_MAPPING_SCHEMA)
    ).toEqual({
        code: 'colors',
        label: 'All colors',
        type: 'array<string>',
        format: null
    });
});
