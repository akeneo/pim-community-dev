import React, {useState} from 'react';
import {Collapse, Field, Helper, Pill, SelectInput} from 'akeneo-design-system';
import {
  filterErrors,
  getAllLocalesFromChannels,
  Section,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {useChannels} from '../../../hooks';
import {LocaleDropdown} from '../../shared/LocaleDropdown';
import {ChannelDropdown} from '../../shared/ChannelDropdown';
import {isDefaultParentSelection, ParentSelection} from './model';

type ParentSelectorProps = {
  selection: ParentSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: ParentSelection) => void;
};

const ParentSelector = ({selection, validationErrors, onSelectionChange}: ParentSelectorProps) => {
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(false);
  const translate = useTranslate();
  const channels = useChannels();
  const locales = getAllLocalesFromChannels(channels);
  const localeErrors = filterErrors(validationErrors, '[locale]');
  const channelErrors = filterErrors(validationErrors, '[channel]');
  const typeErrors = filterErrors(validationErrors, '[type]');

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={
        <>
          {translate('akeneo.syndication.data_mapping_details.sources.selection.title')}
          {0 === validationErrors.length && !isDefaultParentSelection(selection) && <Pill level="primary" />}
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
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            value={selection.type}
            invalid={0 < typeErrors.length}
            onChange={type => {
              if ('label' === type) {
                onSelectionChange({type, channel: channels[0].code, locale: locales[0].code});
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
          <>
            <ChannelDropdown
              channels={channels}
              value={selection.channel}
              validationErrors={channelErrors}
              onChange={updatedValue => onSelectionChange({...selection, channel: updatedValue})}
            >
              <Helper inline={true} level="info">
                {translate('akeneo.syndication.data_mapping_details.sources.selection.parent.information.channel')}
              </Helper>
            </ChannelDropdown>
            <LocaleDropdown
              locales={locales}
              value={selection.locale}
              validationErrors={localeErrors}
              onChange={updatedValue => onSelectionChange({...selection, locale: updatedValue})}
            >
              <Helper inline={true} level="info">
                {translate('akeneo.syndication.data_mapping_details.sources.selection.parent.information.locale')}
              </Helper>
            </LocaleDropdown>
          </>
        )}
      </Section>
    </Collapse>
  );
};

export {ParentSelector};
