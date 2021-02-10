import React from 'react';
import {arrayUnique, CloseIcon, getColor, getFontSize, IconButton, Table, useId} from 'akeneo-design-system';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {Updater} from 'akeneoassetmanager/application/component/library/MassEdit/model/updater';
import {getFieldView} from 'akeneoassetmanager/application/configuration/value';
import {useConfig} from 'akeneoassetmanager/application/hooks/useConfig';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import Channel, {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {getLabel} from 'pimui/js/i18n';
import styled from 'styled-components';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {getLocales} from 'akeneoassetmanager/application/reducer/structure';
import {LocaleDropdown} from './LocaleDropdown';
import {ChannelDropdown} from './ChannelDropdown';

/** @TODO RAC-331 use body style bold */
const AttributeName = styled.label`
  font-size: ${getFontSize('default')};
  font-weight: 700;
  color: ${getColor('brand', 100)};
  font-style: italic;
`;

const InputCell = styled(Table.Cell)`
  width: 400px;
`;
const ContextCell = styled(Table.Cell)`
  width: 380px;
`;
const RemoveCell = styled(Table.Cell)`
  width: 84px;

  & > div {
    display: flex;
    justify-content: center;
  }
`;

type UpdaterRowProps = {
  updater: Updater;
  uiLocale: string;
  onRemove: (updater: Updater) => void;
  onChange: (updater: Updater) => void;
  readOnly: boolean;
  errors: ValidationError[];
  channels: Channel[];
};

const UpdaterRow = ({updater, uiLocale, readOnly, errors, onChange, onRemove, channels}: UpdaterRowProps) => {
  const translate = useTranslate();
  const config = useConfig('value');
  const InputView = getFieldView(config)(updater);
  const handleDataChange = (editionValue: EditionValue) => {
    onChange({...updater, data: editionValue.data});
  };
  const handleLocaleChange = (newLocale: LocaleCode) => {
    onChange({...updater, locale: newLocale});
  };
  const handleChannelChange = (newChannel: ChannelCode) => {
    const channelLocales = getLocales(channels, newChannel);
    const locale =
      channelLocales.find(locale => locale.code === updater.locale) === undefined
        ? channelLocales[0].code
        : updater.locale;

    onChange({...updater, channel: newChannel, locale});
  };

  const locales =
    null !== updater.channel
      ? getLocales(channels, updater.channel)
      : arrayUnique(
          channels.reduce((result, current) => {
            return [...result, ...current.locales];
          }, []),
          (first, second) => first.code === second.code
        );
  const id = useId('updater_row_input_');

  return (
    <Table.Row>
      <Table.Cell>
        <AttributeName htmlFor={id}>
          {getLabel(updater.attribute.labels, uiLocale, updater.attribute.code)}
        </AttributeName>
      </Table.Cell>
      <InputCell expanded={true}>
        <InputView
          id={id}
          canEditData={!readOnly}
          channel={updater.channel}
          locale={updater.locale}
          onChange={handleDataChange}
          onSubmit={() => {}}
          value={updater}
        />
        {errors.map(error => JSON.stringify(error)).join(', ')}
      </InputCell>
      <ContextCell>
        {null !== updater.channel && (
          <ChannelDropdown
            channel={updater.channel}
            uiLocale={uiLocale}
            onChange={handleChannelChange}
            channels={channels}
          />
        )}
        {null !== updater.locale && (
          <LocaleDropdown locale={updater.locale} onChange={handleLocaleChange} locales={locales} />
        )}
        {updater.action}
      </ContextCell>
      <RemoveCell>
        {!readOnly && (
          <IconButton
            level="tertiary"
            icon={<CloseIcon />}
            ghost="borderless"
            title={translate('pim_common.remove')}
            onClick={() => onRemove(updater)}
          />
        )}
      </RemoveCell>
    </Table.Row>
  );
};

export {UpdaterRow};
