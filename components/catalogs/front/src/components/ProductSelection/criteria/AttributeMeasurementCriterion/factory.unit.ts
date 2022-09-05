jest.unmock('./factory');

import factory from './factory';
import {Operator} from '../../models/Operator';

test('it creates a AttributeMeasurementCriterion state with default values', () => {
    expect(factory({field: 'height'})).toMatchObject({
        field: 'height',
        operator: Operator.EQUALS,
        value: null,
        locale: null,
        scope: null,
    });
});

test('it creates a AttributeMeasurementCriterion state with given values', () => {
    expect(
        factory({
            field: 'height',
            operator: Operator.NOT_EQUAL,
            value: {
                amount: 1.5,
                unit: 'm',
            },
            locale: 'en_US',
            scope: 'ecommerce',
        })
    ).toMatchObject({
        field: 'height',
        operator: Operator.NOT_EQUAL,
        value: {
            amount: 1.5,
            unit: 'm',
        },
        locale: 'en_US',
        scope: 'ecommerce',
    });
});

test('it throws if the factory is called without a given field', () => {
    expect(() => factory()).toThrow();
});
