import React from 'react';
import styled from 'styled-components';
import {CloseIcon, getColor, getFontSize, IconButton, SelectInput, List, useId} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {getErrorsForPath} from '@akeneo-pim-community/shared';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {Updater} from 'akeneoassetmanager/application/component/library/MassEdit/model/updater';
import {getFieldView} from 'akeneoassetmanager/application/configuration/value';
import {useConfig} from 'akeneoassetmanager/application/hooks/useConfig';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import Channel, {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {getLabel} from 'pimui/js/i18n';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {getLocaleFromChannel, getLocalesFromChannel} from 'akeneoassetmanager/application/reducer/structure';
import {LocaleDropdown} from 'akeneoassetmanager/application/component/library/MassEdit/components/LocaleDropdown';
import {ChannelDropdown} from 'akeneoassetmanager/application/component/library/MassEdit/components/ChannelDropdown';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';

/** @TODO RAC-331 use body style bold */
const AttributeName = styled.label`
  font-size: ${getFontSize('default')};
  font-weight: 700;
  color: ${getColor('brand', 100)};
  font-style: italic;
`;

const ContextContainer = styled.div`
  display: flex;
  gap: 10px;

  & > * {
    max-width: 120px;
  }
`;

const InputField = styled.div`
  display: flex;
  flex-direction: column;
  width: 100%;
`;

const APPEND_ATTRIBUTE_TYPES = ['option_collection'];

type UpdaterRowProps = {
  updater: Updater;
  uiLocale: string;
  onRemove: (updater: Updater) => void;
  onChange: (updater: Updater) => void;
  readOnly?: boolean;
  errors: ValidationError[];
  channels: Channel[];
};

const UpdaterRow = ({updater, uiLocale, readOnly = false, errors, onChange, onRemove, channels}: UpdaterRowProps) => {
  const translate = useTranslate();
  const config = useConfig('value');
  const InputView = getFieldView(config)(updater);
  const handleDataChange = (editionValue: EditionValue) => {
    onChange({...updater, data: editionValue.data});
  };
  const handleActionChange = (action: typeof updater.action) => {
    onChange({...updater, action});
  };

  const handleLocaleChange = (newLocale: LocaleCode) => {
    onChange({...updater, locale: newLocale});
  };

  const handleChannelChange = (newChannel: ChannelCode) => {
    const locale = null === updater.locale ? null : getLocaleFromChannel(channels, newChannel, updater.locale);

    onChange({...updater, channel: newChannel, locale});
  };

  const locales = getLocalesFromChannel(channels, updater.channel);
  const id = useId('updater_row_input_');

  const rowErrors = getErrorsForPath(errors, `updaters.${updater.id}`);

  return (
    <List.Row multiline>
      <List.Title width="auto">
        <AttributeName htmlFor={id}>
          {getLabel(updater.attribute.labels, uiLocale, updater.attribute.code)}
        </AttributeName>
      </List.Title>
      <List.Cell width={400}>
        <InputField>
          <InputView
            id={id}
            canEditData={!readOnly}
            channel={updater.channel}
            locale={updater.locale}
            onChange={handleDataChange}
            value={updater}
            invalid={0 < rowErrors.length}
          />
          {getErrorsView(rowErrors, `updaters.${updater.id}`)}
        </InputField>
      </List.Cell>
      <List.Cell width={380}>
        <ContextContainer>
          {APPEND_ATTRIBUTE_TYPES.includes(updater.attribute.type) && (
            <SelectInput
              title={translate('pim_asset_manager.asset.mass_edit.select.action')}
              value={updater.action}
              readOnly={readOnly}
              onChange={handleActionChange}
              clearable={false}
              emptyResultLabel={translate('pim_asset_manager.result_counter', {count: 0}, 0)}
            >
              <SelectInput.Option value="replace">
                {translate('pim_asset_manager.asset.mass_edit.action.replace')}
              </SelectInput.Option>
              <SelectInput.Option value="append">
                {translate('pim_asset_manager.asset.mass_edit.action.append')}
              </SelectInput.Option>
            </SelectInput>
          )}
          {null !== updater.channel && (
            <ChannelDropdown
              title={translate('pim_asset_manager.asset.mass_edit.select.channel')}
              readOnly={readOnly}
              channel={updater.channel}
              uiLocale={uiLocale}
              onChange={handleChannelChange}
              channels={channels}
            />
          )}
          {null !== updater.locale && (
            <LocaleDropdown
              title={translate('pim_asset_manager.asset.mass_edit.select.locale')}
              readOnly={readOnly}
              locale={updater.locale}
              onChange={handleLocaleChange}
              locales={locales}
            />
          )}
        </ContextContainer>
      </List.Cell>
      <List.RemoveCell>
        {!readOnly && (
          <IconButton
            level="tertiary"
            icon={<CloseIcon />}
            ghost="borderless"
            title={translate('pim_common.remove')}
            onClick={() => onRemove(updater)}
          />
        )}
      </List.RemoveCell>
    </List.Row>
  );
};

export {UpdaterRow};
