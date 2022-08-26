import {Operator} from '../../models/Operator';
import {Criterion} from '../../models/Criterion';

export type AttributeMeasurementCriterionOperator =
    | typeof Operator.EQUALS
    | typeof Operator.NOT_EQUAL
    | typeof Operator.LOWER_THAN
    | typeof Operator.LOWER_OR_EQUAL_THAN
    | typeof Operator.GREATER_THAN
    | typeof Operator.GREATER_OR_EQUAL_THAN
    | typeof Operator.IS_EMPTY
    | typeof Operator.IS_NOT_EMPTY;

type MeasurementValue = {
    amount: number | string | null;
    unit: string | null;
};

export type AttributeMeasurementCriterionState = {
    field: string;
    operator: AttributeMeasurementCriterionOperator;
    value: MeasurementValue | null;
    locale: string | null;
    scope: string | null;
};

export type AttributeMeasurementCriterion = Criterion<AttributeMeasurementCriterionState>;
