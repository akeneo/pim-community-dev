jest.unmock('./factory');

import factory from './factory';
import {Operator} from '../../models/Operator';

test('it creates a AttributeMultiSelectCriterion state with default values', () => {
    expect(factory({field: 'materials'})).toMatchObject({
        field: 'materials',
        operator: Operator.IN_LIST,
        value: [],
        locale: null,
        scope: null,
    });
});

test('it creates a AttributeMultiSelectCriterion state with given values', () => {
    expect(
        factory({
            field: 'materials',
            operator: Operator.NOT_IN_LIST,
            value: ['wool'],
            locale: 'en_US',
            scope: 'ecommerce',
        })
    ).toMatchObject({
        field: 'materials',
        operator: Operator.NOT_IN_LIST,
        value: ['wool'],
        locale: 'en_US',
        scope: 'ecommerce',
    });
});

test('it throws if the factory is called without a given field', () => {
    expect(() => factory()).toThrow();
});
