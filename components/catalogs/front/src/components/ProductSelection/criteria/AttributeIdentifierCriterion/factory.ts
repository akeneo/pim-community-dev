import {AttributeIdentifierCriterionState} from './types';
import {Operator} from '../../models/Operator';

export default (state?: Partial<AttributeIdentifierCriterionState>): AttributeIdentifierCriterionState => {
    if (!state?.field) {
        throw Error('You need to specify the attribute code when calling the attribute criterion factory');
    }

    return {
        field: state.field,
        operator: state?.operator ?? Operator.IN_LIST,
        value: state?.value ?? [],
        locale: state?.locale ?? null,
        scope: state?.scope ?? null,
    };
};
