import React from 'react';
import {filterErrors, Section, TextField, useTranslate, ValidationError} from '@akeneo-pim-community/shared';

type BooleanReplacementOperation = {
  type: 'replacement';
  mapping: {
    true: string;
    false: string;
  };
};

type BooleanReplacementProps = {
  operation: BooleanReplacementOperation;
  validationErrors: ValidationError[];
  onOperationChange: (updatedOperation: BooleanReplacementOperation) => void;
};

const BooleanReplacement = ({operation, validationErrors, onOperationChange}: BooleanReplacementProps) => {
  const translate = useTranslate();

  return (
    <Section>
      <TextField
        value={operation.mapping.true}
        label={translate('akeneo.tailored_export.column_details.sources.operation.replacement.enabled')}
        errors={filterErrors(validationErrors, '[mapping][true]')}
        onChange={enabledValue =>
          onOperationChange({...operation, mapping: {...operation.mapping, true: enabledValue}})
        }
      />
      <TextField
        value={operation.mapping.false}
        label={translate('akeneo.tailored_export.column_details.sources.operation.replacement.disabled')}
        errors={filterErrors(validationErrors, '[mapping][false]')}
        onChange={disabledValue =>
          onOperationChange({...operation, mapping: {...operation.mapping, false: disabledValue}})
        }
      />
    </Section>
  );
};

export {BooleanReplacement};
export type {BooleanReplacementOperation};
