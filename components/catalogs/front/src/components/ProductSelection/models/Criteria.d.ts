import {Operator} from './Operator';
import {FC} from 'react';

export type CriteriaModule<Values> = {
    value: Values;
    onChange: (values: Values) => void;
};

export type Criteria = {
    module: FC<CriteriaModule<any>>;
    id: string;
    field: string;
    operator: Operator;
    value?: any;
};
