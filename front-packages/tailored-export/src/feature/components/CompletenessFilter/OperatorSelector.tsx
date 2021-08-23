import React from 'react';
import {useTranslate, Section, ValidationError} from '@akeneo-pim-community/shared';
import {Field, Helper, SelectInput} from 'akeneo-design-system';

type Operator = string;
type OperatorSelectorProps = {
  availableOperators: string[];
  operator: Operator;
  onChange: (newOperator: Operator) => void;
  validationErrors: ValidationError[];
};
const OperatorSelector = ({availableOperators, operator, onChange, validationErrors}: OperatorSelectorProps) => {
  const translate = useTranslate();

  return (
    <Section>
      <Field label={translate('pim_enrich.export.product.filter.completeness.title')}>
        <SelectInput
          clearable={false}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          value={operator}
          onChange={onChange}
        >
          {availableOperators.map((operator: Operator) => (
            <SelectInput.Option
              key={operator}
              title={translate(`pim_enrich.export.product.filter.completeness.operators.${operator}`)}
              value={operator}
            >
              {translate(`pim_enrich.export.product.filter.completeness.operators.${operator}`)}
            </SelectInput.Option>
          ))}
        </SelectInput>
        {validationErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
    </Section>
  );
};

export {OperatorSelector};
export type {Operator};
