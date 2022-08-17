jest.unmock('./factory');

import factory from './factory';
import {Operator} from '../../models/Operator';

test('it creates a CompletenessCriterion state with default values', () => {
    expect(factory()).toEqual({
        field: 'completeness',
        operator: Operator.EQUALS,
        value: 100,
        scope: null,
        locale: null,
    });
});

test('it creates a CompletenessCriterion state with given values', () => {
    expect(
        factory({
            operator: Operator.GREATER_THAN,
            value: 50,
            scope: 'scope_code',
            locale: 'locale_code',
        })
    ).toEqual({
        field: 'completeness',
        operator: Operator.GREATER_THAN,
        value: 50,
        scope: 'scope_code',
        locale: 'locale_code',
    });
});
