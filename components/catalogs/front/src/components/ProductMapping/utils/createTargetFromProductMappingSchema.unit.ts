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
                    type: 'string',
                    enum: ['Red', 'Green', 'Blue'],
                },
                title: 'All colors',
                description: 'This is the list of the colors',
            },
        },
    };

    expect(createTargetFromProductMappingSchema('colors', PRODUCT_MAPPING_SCHEMA)).toEqual({
        code: 'colors',
        label: 'All colors',
        type: 'array<string>',
        format: null,
        enum: ['Red', 'Green', 'Blue'],
        description: 'This is the list of the colors',
    });
});

test('it creates target of array type with missing items', () => {
    const PRODUCT_MAPPING_SCHEMA = {
        properties: {
            uuid: {
                type: 'string',
            },
            colors: {
                type: 'array',
                title: 'All colors',
            },
        },
    };

    expect(createTargetFromProductMappingSchema('colors', PRODUCT_MAPPING_SCHEMA)).toEqual({
        code: 'colors',
        label: 'All colors',
        type: 'array<>',
        format: null,
    });
});

test('it creates target with format', () => {
    const PRODUCT_MAPPING_SCHEMA = {
        properties: {
            uuid: {
                type: 'string',
            },
            is_released: {
                type: 'string',
                format: 'date-time',
            },
        },
    };

    expect(createTargetFromProductMappingSchema('is_released', PRODUCT_MAPPING_SCHEMA)).toEqual({
        code: 'is_released',
        label: 'is_released',
        type: 'string',
        format: 'date-time',
    });
});

test('it creates target with minLength and maxLength', () => {
    const PRODUCT_MAPPING_SCHEMA = {
        properties: {
            uuid: {
                type: 'string',
            },
            name: {
                type: 'string',
                minLength: 50,
                maxLength: 200,
            },
        },
    };

    expect(createTargetFromProductMappingSchema('name', PRODUCT_MAPPING_SCHEMA)).toEqual({
        code: 'name',
        label: 'name',
        type: 'string',
        format: null,
        minLength: 50,
        maxLength: 200,
    });
});

test('it creates target with pattern', () => {
    const PRODUCT_MAPPING_SCHEMA = {
        properties: {
            uuid: {
                type: 'string',
            },
            identifier: {
                type: 'string',
                pattern: '[A-Za-z0-9-_]+',
            },
        },
    };

    expect(createTargetFromProductMappingSchema('identifier', PRODUCT_MAPPING_SCHEMA)).toEqual({
        code: 'identifier',
        label: 'identifier',
        type: 'string',
        format: null,
        pattern: '[A-Za-z0-9-_]+',
    });
});

test('it creates target with minimum and maximum', () => {
    const PRODUCT_MAPPING_SCHEMA = {
        properties: {
            uuid: {
                type: 'string',
            },
            height: {
                type: 'number',
                minimum: 10,
                maximum: 100,
            },
        },
    };

    expect(createTargetFromProductMappingSchema('height', PRODUCT_MAPPING_SCHEMA)).toEqual({
        code: 'height',
        label: 'height',
        type: 'number',
        format: null,
        minimum: 10,
        maximum: 100,
    });
});

test('it creates target with enum', () => {
    const PRODUCT_MAPPING_SCHEMA = {
        properties: {
            uuid: {
                type: 'string',
            },
            size: {
                type: 'string',
                enum: ['S', 'M', 'L'],
            },
        },
    };

    expect(createTargetFromProductMappingSchema('size', PRODUCT_MAPPING_SCHEMA)).toEqual({
        code: 'size',
        label: 'size',
        type: 'string',
        format: null,
        enum: ['S', 'M', 'L'],
    });
});
