jest.unmock('./factory');

import factory from './factory';
import {Operator} from '../../models/Operator';

test('it creates a CategoryCriterion state with default values', () => {
    expect(factory()).toMatchObject({
        field: 'categories',
        operator: Operator.IN_LIST,
        value: [],
    });
});

test('it creates a CategoryCriterion state with given values', () => {
    expect(
        factory({
            operator: Operator.UNCLASSIFIED,
            value: [],
        })
    ).toMatchObject({
        field: 'categories',
        operator: Operator.UNCLASSIFIED,
        value: [],
    });
});
