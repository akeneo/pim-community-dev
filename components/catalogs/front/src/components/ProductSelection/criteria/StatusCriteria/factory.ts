import {StatusCriteria, StatusCriteriaState} from './types';
import {StatusCriteria as Component} from './StatusCriteria';
import {Operator} from '../../models/Operator';

const defaultValues: Pick<StatusCriteria, 'module' | 'field' | 'operator' | 'value'> = {
    module: Component,
    field: 'status',
    operator: Operator.EQUALS,
    value: true,
};

export default (state?: StatusCriteriaState): StatusCriteria => ({
    ...defaultValues,
    ...state,
    id: (Math.random() + 1).toString(36).substring(7),
});
