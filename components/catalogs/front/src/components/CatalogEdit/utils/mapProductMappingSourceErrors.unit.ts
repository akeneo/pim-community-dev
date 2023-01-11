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
    ];
    const keys = ['uuid', 'name', 'body_html', 'erp_name'];
    expect(mapProductMappingSourceErrors(errors, keys)).toEqual({
        uuid: {
            source: undefined,
            scope: undefined,
            locale: undefined,
        },
        name: {
            source: undefined,
            scope: undefined,
            locale: 'Locale invalid.',
        },
        body_html: {
            source: undefined,
            scope: undefined,
            locale: undefined,
        },
        erp_name: {
            source: 'Source invalid.',
            scope: undefined,
            locale: undefined,
        },
    });
});
