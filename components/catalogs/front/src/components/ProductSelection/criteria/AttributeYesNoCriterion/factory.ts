import {AttributeYesNoCriterionState} from './types';
import {Operator} from '../../models/Operator';

export default (state?: Partial<AttributeYesNoCriterionState>): AttributeYesNoCriterionState => {
    if (!state?.field) {
        throw Error('You need to specific the attribute code when calling the attribute criterion factory');
    }

    return {
        field: state.field,
        operator: state?.operator ?? Operator.EQUALS,
        value: state?.value ?? null,
        locale: state?.locale ?? null,
        scope: state?.scope ?? null,
    };
};
