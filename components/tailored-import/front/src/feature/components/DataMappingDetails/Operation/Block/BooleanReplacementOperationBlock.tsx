import React from 'react';
import {Block, Field, Helper, TagInput, useBooleanState, uuid} from 'akeneo-design-system';
import {useTranslate, Section, filterErrors} from '@akeneo-pim-community/shared';
import {isBooleanReplacementValues, BooleanReplacementValues} from '../../../../models';
import {OperationBlockProps} from './OperationBlockProps';

const BOOLEAN_REPLACEMENT_OPERATION_TYPE = 'boolean_replacement';

type BooleanReplacementOperation = {
  uuid: string;
  type: typeof BOOLEAN_REPLACEMENT_OPERATION_TYPE;
  mapping: BooleanReplacementValues;
};

const getDefaultBooleanReplacementOperation = (): BooleanReplacementOperation => ({
  uuid: uuid(),
  type: BOOLEAN_REPLACEMENT_OPERATION_TYPE,
  mapping: {
    false: ['0'],
    true: ['1'],
  },
});

const isBooleanReplacementOperation = (operation?: any): operation is BooleanReplacementOperation =>
  undefined !== operation &&
  'type' in operation &&
  BOOLEAN_REPLACEMENT_OPERATION_TYPE === operation.type &&
  'mapping' in operation &&
  isBooleanReplacementValues(operation.mapping);

const BooleanReplacementOperationBlock = ({
  operation,
  isLastOperation,
  onChange,
  validationErrors,
}: OperationBlockProps) => {
  if (!isBooleanReplacementOperation(operation)) {
    throw new Error('BooleanReplacementOperationBlock can only be used with BooleanReplacementOperation');
  }
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState(isLastOperation);

  const handleMappingChange = (key: string, values: string[]) =>
    onChange({...operation, mapping: {...operation.mapping, [key]: values}});

  const mappingErrors = filterErrors(validationErrors, '[mapping]');
  const trueErrors = filterErrors(mappingErrors, '[true]');
  const falseErrors = filterErrors(mappingErrors, '[false]');

  return (
    <Block
      collapseButtonLabel={translate('akeneo.tailored_import.data_mapping.operations.common.collapse')}
      onCollapse={isOpen ? close : open}
      isOpen={isOpen}
      title={translate('akeneo.tailored_import.data_mapping.operations.boolean_replacement.title')}
    >
      <Section>
        <Field label={translate('akeneo.tailored_import.data_mapping.operations.boolean_replacement.field.yes_value')}>
          <TagInput
            value={operation.mapping.true}
            onChange={yesValues => handleMappingChange('true', yesValues)}
            invalid={0 < trueErrors.length}
            placeholder={translate('akeneo.tailored_import.data_mapping.operations.replacement.to_placeholder')}
          />
          {trueErrors.map((error, index) => (
            <Helper key={index} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Field>
        <Field label={translate('akeneo.tailored_import.data_mapping.operations.boolean_replacement.field.no_value')}>
          <TagInput
            value={operation.mapping.false}
            onChange={noValues => handleMappingChange('false', noValues)}
            invalid={0 < falseErrors.length}
            placeholder={translate('akeneo.tailored_import.data_mapping.operations.replacement.to_placeholder')}
          />
          {falseErrors.map((error, index) => (
            <Helper key={index} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Field>
      </Section>
    </Block>
  );
};

export {BOOLEAN_REPLACEMENT_OPERATION_TYPE, BooleanReplacementOperationBlock, getDefaultBooleanReplacementOperation};
export type {BooleanReplacementOperation};
