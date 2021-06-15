import React from "react";
import {useTranslate, Section} from '@akeneo-pim-community/shared';
import {Field, SelectInput} from 'akeneo-design-system';

const availableOperators = [
    'ALL',
    'GREATER OR EQUALS THAN ON AT LEAST ONE LOCALE',
    'GREATER OR EQUALS THAN ON ALL LOCALES',
    'LOWER THAN ON ALL LOCALES'
];
type Operator = typeof availableOperators[number];
const isValidOperator = (operatorToCheck: unknown): operatorToCheck is Operator => {
    return typeof operatorToCheck === 'string' && availableOperators.includes(operatorToCheck);
}

type OperatorSelectorProps = {
    operator: Operator;
    onChange: (newOperator: Operator) => void;
}
const OperatorSelector = ({operator, onChange}: OperatorSelectorProps) => {
    const translate = useTranslate();

    return (
        <Section>
            <Field label={translate('pim_connector.export.completeness.selector.label')}>
                <SelectInput
                    clearable={false}
                    emptyResultLabel={translate('pim_common.no_result')}
                    openLabel={translate('pim_common.open')}
                    value={operator}
                    onChange={newOperator => isValidOperator(newOperator) && onChange(newOperator)}
                >
                    {availableOperators.map((operator: string)=> (
                        <SelectInput.Option key={operator} title={translate(`pim_enrich.export.product.filter.completeness.operators.${operator}`)} value={operator}>
                            {translate(`pim_enrich.export.product.filter.completeness.operators.${operator}`)}
                        </SelectInput.Option>
                    ))}
                </SelectInput>
            </Field>
        </Section>

    )
};

export {OperatorSelector};
export type {Operator};
