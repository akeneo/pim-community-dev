import React from 'react';
import {Block, Field, TagInput, useBooleanState, uuid} from 'akeneo-design-system';
import {useTranslate, Section} from '@akeneo-pim-community/shared';
import {isBooleanReplacementValues, BooleanReplacementValues} from '../../../../models';
import {OperationBlockProps} from './OperationBlockProps';

const BOOLEAN_REPLACEMENT_OPERATION_TYPE = 'boolean_replacement';

type BooleanReplacementOperation = {
  uuid: string;
  type: typeof BOOLEAN_REPLACEMENT_OPERATION_TYPE;
  mapping: BooleanReplacementValues;
};

const getDefaultBooleanReplacementOperation = (): BooleanReplacementOperation => {
  return {
    uuid: uuid(),
    type: BOOLEAN_REPLACEMENT_OPERATION_TYPE,
    mapping: {
      false: ['0'],
      true: ['1'],
      null: ['N/A'],
    },
  };
};

const isBooleanReplacementOperation = (operation?: any): operation is BooleanReplacementOperation =>
  undefined !== operation &&
  'type' in operation &&
  BOOLEAN_REPLACEMENT_OPERATION_TYPE === operation.type &&
  'mapping' in operation &&
  isBooleanReplacementValues(operation.mapping);

const BooleanReplacementOperationBlock = ({operation, isLastOperation, onChange}: OperationBlockProps) => {
  if (!isBooleanReplacementOperation(operation)) {
    throw new Error('BooleanReplacementOperationBlock can only be used with BooleanReplacementOperation');
  }
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState(isLastOperation);

  const handleMappingChange = (newMapping: BooleanReplacementValues) => {
    onChange({...operation, mapping: newMapping});
  };

  return (
    <Block
      collapseButtonLabel={translate('akeneo.tailored_import.data_mapping.operations.boolean_replacement.collapse')}
      onCollapse={isOpen ? close : open}
      isOpen={isOpen}
      title={translate(`akeneo.tailored_import.data_mapping.operations.boolean_replacement.title`)}
    >
      <Section>
        <Field label={translate(`akeneo.tailored_import.data_mapping.operations.boolean_replacement.field.yes_value`)}>
          <TagInput
            value={operation.mapping.true}
            onChange={newValue => handleMappingChange({...operation.mapping, true: newValue})}
          />
        </Field>
        <Field label={translate(`akeneo.tailored_import.data_mapping.operations.boolean_replacement.field.no_value`)}>
          <TagInput
            value={operation.mapping.false}
            onChange={newValue => handleMappingChange({...operation.mapping, false: newValue})}
          />
        </Field>
        <Field label={translate(`akeneo.tailored_import.data_mapping.operations.boolean_replacement.field.null_value`)}>
          <TagInput
            value={operation.mapping.null}
            onChange={newValue => handleMappingChange({...operation.mapping, null: newValue})}
          />
        </Field>
      </Section>
    </Block>
  );
};

export {BOOLEAN_REPLACEMENT_OPERATION_TYPE, BooleanReplacementOperationBlock, getDefaultBooleanReplacementOperation};
export type {BooleanReplacementOperation};
