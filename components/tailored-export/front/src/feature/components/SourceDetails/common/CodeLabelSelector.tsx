import React, {useState} from 'react';
import {Collapse, Field, Helper, Pill, SelectInput} from 'akeneo-design-system';
import {
  filterErrors,
  getAllLocalesFromChannels,
  LocaleCode,
  Section,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {useChannels} from '../../../hooks';
import {LocaleDropdown} from '../../LocaleDropdown';

type CodeLabelSelection =
  | {
      type: 'code';
    }
  | {
      type: 'label';
      locale: LocaleCode;
    };

const isCodeLabelSelection = (selection: any): selection is CodeLabelSelection =>
  'type' in selection && (selection.type === 'code' || (selection.type === 'label' && 'locale' in selection));

const getDefaultCodeLabelSelection = (): CodeLabelSelection => ({
  type: 'code',
});

const isDefaultCodeLabelSelection = (selection?: CodeLabelSelection): boolean => 'code' === selection?.type;

type CodeLabelSelectorProps = {
  selection: CodeLabelSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: CodeLabelSelection) => void;
};

const CodeLabelSelector = ({selection, validationErrors, onSelectionChange}: CodeLabelSelectorProps) => {
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(false);
  const translate = useTranslate();
  const channels = useChannels();
  const locales = getAllLocalesFromChannels(channels);
  const localeErrors = filterErrors(validationErrors, '[locale]');
  const typeErrors = filterErrors(validationErrors, '[type]');

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={
        <>
          {translate('akeneo.tailored_export.column_details.sources.selection.title')}
          {0 === validationErrors.length && !isDefaultCodeLabelSelection(selection) && <Pill level="primary" />}
          {0 < validationErrors.length && <Pill level="danger" />}
        </>
      }
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <Section>
        <Field label={translate('pim_common.type')}>
          <SelectInput
            clearable={false}
            invalid={0 < typeErrors.length}
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            value={selection.type}
            onChange={type => {
              if ('label' === type) {
                onSelectionChange({type, locale: locales[0].code});
              } else if ('code' === type) {
                onSelectionChange({type});
              }
            }}
          >
            <SelectInput.Option title={translate('pim_common.label')} value="label">
              {translate('pim_common.label')}
            </SelectInput.Option>
            <SelectInput.Option title={translate('pim_common.code')} value="code">
              {translate('pim_common.code')}
            </SelectInput.Option>
          </SelectInput>
          {typeErrors.map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Field>
        {'label' === selection.type && (
          <LocaleDropdown
            value={selection.locale}
            validationErrors={localeErrors}
            locales={locales}
            onChange={updatedValue => onSelectionChange({...selection, locale: updatedValue})}
          />
        )}
      </Section>
    </Collapse>
  );
};

export {CodeLabelSelector, getDefaultCodeLabelSelection, isCodeLabelSelection};
export type {CodeLabelSelection};
