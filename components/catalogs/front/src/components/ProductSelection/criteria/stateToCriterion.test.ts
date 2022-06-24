jest.unmock('./stateToCriterion');
jest.unmock('./StatusCriterion');

import {CriterionState} from '../models/Criteria';
import {stateToCriterion} from './stateToCriterion';
import {StatusCriterionState} from './StatusCriterion';

test('test it throws on unknown criterion', () => {
    expect(() => {
        stateToCriterion({
            field: 'unknown',
            operator: '=',
        } as CriterionState);
    }).toThrow(Error);
});

test('test it maps status criterion', () => {
    const result = stateToCriterion({
        field: 'enabled',
        operator: '=',
        value: true,
    } as StatusCriterionState);

    expect(result).toMatchObject({
        id: expect.any(String),
        module: expect.any(Function),
        state: {
            field: 'enabled',
            operator: '=',
            value: true,
        },
    });
});
