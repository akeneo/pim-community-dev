jest.unmock('./mapProductMappingSourceErrors');
jest.unmock('./findFirstError');

import {mapProductMappingSourceErrors} from './mapProductMappingSourceErrors';
import {CatalogFormErrors} from '../models/CatalogFormErrors';

test('it maps API errors to ProductMapping errors indexed by key', () => {
    const errors: CatalogFormErrors = [
        {
            propertyPath: 'productMapping[name][locale]',
            message: 'Locale invalid.',
        },
        {
            propertyPath: 'productMapping[erp_name][source]',
            message: 'Source invalid.',
        },
        {
            propertyPath: 'productMapping[body_html][scope]',
            message: 'Scope invalid.',
        },
        {
            propertyPath: 'productMapping[size][parameters][label_locale]',
            message: 'Locale invalid.',
        },
        {
            propertyPath: 'productMapping[size][parameters][unit]',
            message: 'Measurement unit invalid.',
        },
    ];
    const keys = ['uuid', 'name', 'body_html', 'erp_name', 'size'];
    expect(mapProductMappingSourceErrors(errors, keys)).toEqual({
        uuid: {
            source: undefined,
            scope: undefined,
            locale: undefined,
            parameters: {
                label_locale: undefined,
            },
        },
        name: {
            source: undefined,
            scope: undefined,
            locale: 'Locale invalid.',
            parameters: {
                label_locale: undefined,
            },
        },
        body_html: {
            source: undefined,
            scope: 'Scope invalid.',
            locale: undefined,
            parameters: {
                label_locale: undefined,
            },
        },
        erp_name: {
            source: 'Source invalid.',
            scope: undefined,
            locale: undefined,
            parameters: {
                label_locale: undefined,
            },
        },
        size: {
            source: undefined,
            scope: undefined,
            locale: undefined,
            parameters: {
                label_locale: 'Locale invalid.',
            },
        },
        weight: {
            source: undefined,
            scope: undefined,
            locale: undefined,
            parameters: {
                unit: 'Measurement unit invalid.',
            },
        },
    });
});
