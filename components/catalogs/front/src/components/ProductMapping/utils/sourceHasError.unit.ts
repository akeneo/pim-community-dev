import {SourceErrors} from '../models/SourceErrors';
import {sourceHasError} from './sourceHasError';

jest.unmock('./sourceHasError');

const tests: {errors: SourceErrors | undefined; result: boolean}[] = [
    {
        errors: undefined,
        result: false,
    },
    {
        errors: {
            source: undefined,
            locale: undefined,
            scope: undefined,
        },
        result: false,
    },
    {
        errors: {
            source: 'Something`s wrong',
            locale: undefined,
            scope: undefined,
        },
        result: true,
    },
    {
        errors: {
            source: undefined,
            locale: 'Something`s wrong',
            scope: undefined,
        },
        result: true,
    },
    {
        errors: {
            source: undefined,
            locale: undefined,
            scope: 'Something`s wrong',
        },
        result: true,
    },
    {
        errors: {
            source: undefined,
            locale: undefined,
            scope: undefined,
            parameters: {
                label_locale: 'Something`s wrong',
            },
        },
        result: true,
    },
];

test.each(tests)('it returns either the source has an error or not #%#', ({errors, result}) => {
    expect(sourceHasError(errors)).toEqual(result);
});
