import {AttributeDateCriterionState} from './types';
import {Operator} from '../../models/Operator';

export default (state?: Partial<AttributeDateCriterionState>): AttributeDateCriterionState => {
    if (!state?.field) {
        throw Error('You need to specify the attribute code when calling the attribute criterion factory');
    }

    return {
        field: state.field,
        operator: state?.operator ?? Operator.IS_NOT_EMPTY,
        value: state?.value ?? null,
        locale: state?.locale ?? null,
        scope: state?.scope ?? null,
    };
};
