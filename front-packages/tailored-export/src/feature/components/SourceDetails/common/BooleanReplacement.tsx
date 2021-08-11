import React from 'react';
import {filterErrors, Section, TextField, useTranslate, ValidationError} from '@akeneo-pim-community/shared';

type BooleanReplacementOperation = {
  type: 'replacement';
  mapping: {
    true: string;
    false: string;
  };
};

const isBooleanReplacementOperation = (operation?: any) =>
  undefined !== operation &&
  'type' in operation &&
  'replacement' === operation.type &&
  'mapping' in operation &&
  'true' in operation.mapping &&
  'false' in operation.mapping;

const getDefaultBooleanReplacementOperation = (): BooleanReplacementOperation => ({
  type: 'replacement',
  mapping: {
    true: '1',
    false: '0',
  },
});

const isDefaultBooleanReplacementOperation = (operation?: BooleanReplacementOperation) =>
  operation?.type === 'replacement' && operation.mapping.true === '1' && operation.mapping.false === '0';

type BooleanReplacementProps = {
  trueLabel: string;
  falseLabel: string;
  operation?: BooleanReplacementOperation;
  validationErrors: ValidationError[];
  onOperationChange: (updatedOperation?: BooleanReplacementOperation) => void;
};

const BooleanReplacement = ({
  trueLabel,
  falseLabel,
  operation = getDefaultBooleanReplacementOperation(),
  validationErrors,
  onOperationChange,
}: BooleanReplacementProps) => {
  const translate = useTranslate();

  return (
    <Section>
      <TextField
        value={operation.mapping.true}
        label={trueLabel}
        placeholder={translate('akeneo.tailored_export.column_details.sources.operation.replacement.placeholder')}
        errors={filterErrors(validationErrors, '[mapping][true]')}
        onChange={trueReplacement => {
          const newOperation = {...operation, mapping: {...operation.mapping, true: trueReplacement}};

          onOperationChange(isDefaultBooleanReplacementOperation(newOperation) ? undefined : newOperation);
        }}
      />
      <TextField
        value={operation.mapping.false}
        label={falseLabel}
        placeholder={translate('akeneo.tailored_export.column_details.sources.operation.replacement.placeholder')}
        errors={filterErrors(validationErrors, '[mapping][false]')}
        onChange={falseReplacement => {
          const newOperation = {...operation, mapping: {...operation.mapping, false: falseReplacement}};

          onOperationChange(isDefaultBooleanReplacementOperation(newOperation) ? undefined : newOperation);
        }}
      />
    </Section>
  );
};

export {BooleanReplacement, getDefaultBooleanReplacementOperation, isBooleanReplacementOperation};
export type {BooleanReplacementOperation};
