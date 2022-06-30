jest.unmock('./index');

import criterion from './index';
import {Operator} from '../../models/Operator';

test('it provides a component and the state factory', () => {
    expect(criterion).toMatchObject({
        component: expect.any(Function),
        factory: expect.any(Function),
    });
});

test('it creates a StatusCriterion state with empty values', () => {
    expect(criterion.factory()).toMatchObject({
        field: 'enabled',
        operator: Operator.EQUALS,
        value: true,
    });
});

test('it creates a StatusCriterion state with given values', () => {
    expect(
        criterion.factory({
            operator: Operator.NOT_EQUAL,
            value: false,
        })
    ).toMatchObject({
        field: 'enabled',
        operator: Operator.NOT_EQUAL,
        value: false,
    });
});
