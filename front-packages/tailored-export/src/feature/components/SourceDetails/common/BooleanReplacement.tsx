import React, {useState} from 'react';
import {filterErrors, Section, TextField, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {Collapse, Pill} from 'akeneo-design-system';

type BooleanReplacementOperation = {
  type: 'replacement';
  mapping: {
    true: string;
    false: string;
  };
};

const isBooleanReplacementOperation = (operation?: any): operation is BooleanReplacementOperation =>
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

const isDefaultBooleanReplacementOperation = (operation?: BooleanReplacementOperation): boolean =>
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
  const [isReplacementCollapsed, toggleReplacementCollapse] = useState<boolean>(false);

  return (
    <Collapse
      collapseButtonLabel={isReplacementCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={
        <>
          {translate('akeneo.tailored_export.column_details.sources.operation.replacement.title')}
          {0 === validationErrors.length && !isDefaultBooleanReplacementOperation(operation) && (
            <Pill level="primary" />
          )}
          {0 < validationErrors.length && <Pill level="danger" />}
        </>
      }
      isOpen={isReplacementCollapsed}
      onCollapse={toggleReplacementCollapse}
    >
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
    </Collapse>
  );
};

export {BooleanReplacement, getDefaultBooleanReplacementOperation, isBooleanReplacementOperation};
export type {BooleanReplacementOperation};
