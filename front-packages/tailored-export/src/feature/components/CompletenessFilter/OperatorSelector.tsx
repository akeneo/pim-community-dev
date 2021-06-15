import React from "react";
import {useTranslate} from '@akeneo-pim-community/shared';

type Operator =
    'ALL'
    | 'GREATER OR EQUALS THAN ON AT LEAST ONE LOCALE'
    | 'GREATER OR EQUALS THAN ON ALL LOCALES'
    | 'LOWER THAN ON ALL LOCALES'
    | any;

type OperatorSelectorProps = {
    operator: Operator;
    onChange: (newOperator: Operator) => void;
}
const OperatorSelector = ({}: OperatorSelectorProps) => {
    const translate = useTranslate();

    return (
      <span>
        {translate('pim_connector.export.completeness.selector.label')}
      </span>
    );
};

export {OperatorSelector};
export type {Operator}
