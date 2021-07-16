import React from 'react';
import {useTranslate, Section, ValidationError} from '@akeneo-pim-community/shared';
import {Helper, SelectInput} from 'akeneo-design-system';

type Operator = string;
type OperatorSelectorProps = {
  availableOperators: string[];
  operator: Operator;
  onChange: (newOperator: Operator) => void;
  validationErrors: ValidationError[];
};

// TODO: Put in common with completeness operatorSelector ?
const OperatorSelector = ({availableOperators, operator, onChange, validationErrors}: OperatorSelectorProps) => {
  const translate = useTranslate();

  return (
    <Section>
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
            title={translate(`pim_enrich.export.product.filter.quality_score.operators.${operator}`)}
            value={operator}
          >
            {translate(`pim_enrich.export.product.filter.quality_score.operators.${operator}`)}
          </SelectInput.Option>
        ))}
      </SelectInput>
      {validationErrors.map((error, index) => (
        <Helper key={index} inline={true} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
    </Section>
  );
};

export {OperatorSelector};
export type {Operator};
