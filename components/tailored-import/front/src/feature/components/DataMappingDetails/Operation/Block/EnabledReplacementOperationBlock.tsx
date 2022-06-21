import React from 'react';
import {Block, Field, Helper, TagInput, useBooleanState, uuid} from 'akeneo-design-system';
import {useTranslate, Section, filterErrors} from '@akeneo-pim-community/shared';
import {isBooleanReplacementValues, BooleanReplacementValues} from '../../../../models';
import {OperationBlockProps} from './OperationBlockProps';

const ENABLED_REPLACEMENT_OPERATION_TYPE = 'enabled_replacement';

type EnabledReplacementOperation = {
  uuid: string;
  type: typeof ENABLED_REPLACEMENT_OPERATION_TYPE;
  mapping: BooleanReplacementValues;
};

const getDefaultEnabledReplacementOperation = (): EnabledReplacementOperation => ({
  uuid: uuid(),
  type: ENABLED_REPLACEMENT_OPERATION_TYPE,
  mapping: {
    false: ['0'],
    true: ['1'],
  },
});

const isEnabledReplacementOperation = (operation?: any): operation is EnabledReplacementOperation =>
  undefined !== operation &&
  'type' in operation &&
  ENABLED_REPLACEMENT_OPERATION_TYPE === operation.type &&
  'mapping' in operation &&
  isBooleanReplacementValues(operation.mapping);

const EnabledReplacementOperationBlock = ({
  operation,
  isLastOperation,
  onChange,
  validationErrors,
}: OperationBlockProps) => {
  if (!isEnabledReplacementOperation(operation)) {
    throw new Error('EnabledReplacementOperationBlock can only be used with EnabledReplacementOperation');
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
      title={translate('akeneo.tailored_import.data_mapping.operations.enabled_replacement.title')}
    >
      <Section>
        <Field
          label={translate('akeneo.tailored_import.data_mapping.operations.enabled_replacement.field.enabled_value')}
        >
          <TagInput
            value={operation.mapping.true}
            onChange={enabledValues => handleMappingChange('true', enabledValues)}
            invalid={0 < trueErrors.length}
            placeholder={translate('akeneo.tailored_import.data_mapping.operations.replacement.to_placeholder')}
          />
          {trueErrors.map((error, index) => (
            <Helper key={index} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Field>
        <Field
          label={translate('akeneo.tailored_import.data_mapping.operations.enabled_replacement.field.disabled_value')}
        >
          <TagInput
            value={operation.mapping.false}
            onChange={disabledValues => handleMappingChange('false', disabledValues)}
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

export {ENABLED_REPLACEMENT_OPERATION_TYPE, EnabledReplacementOperationBlock, getDefaultEnabledReplacementOperation};
export type {EnabledReplacementOperation};
