import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import styled, {css} from 'styled-components';
import Line, {LineStatus} from 'akeneoassetmanager/application/asset-upload/model/line';
import CrossIcon from 'akeneoassetmanager/application/component/app/icon/close';
import {akeneoTheme, ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import RowStatus from 'akeneoassetmanager/application/asset-upload/component/row-status';
import {getAllErrorsOfLineByTarget, getStatusFromLine} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import Spacer from 'akeneoassetmanager/application/component/app/spacer';
import {ColumnWidths} from 'akeneoassetmanager/application/asset-upload/component/line-list';
import WarningIcon from 'akeneoassetmanager/application/component/app/icon/warning';
import Channel, {getChannelLabel} from 'akeneoassetmanager/domain/model/channel';
import Select2 from 'akeneoassetmanager/application/component/app/select2';
import Locale, {LocaleCode} from 'akeneoassetmanager/domain/model/locale';

const Container = styled.div<{status?: LineStatus}>`
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
  padding: 15px 0;

  ${props =>
    props.status === LineStatus.Invalid &&
    css`
      background: ${(props: ThemedProps<void>) => props.theme.color.red20};
      border-bottom: 1px solid #ffffff;
    `}
`;
const Cells = styled.div`
  display: flex;
  justify-content: space-between;
  align-items: center;
`;
const Cell = styled.div<{width?: number}>`
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140};
  display: flex;
  flex-direction: column;
  flex-grow: 0;
  flex-shrink: 0;
  padding: 0 15px;

  ${props =>
    props.width !== undefined &&
    css`
      overflow: hidden;
      width: ${props.width}px;
    `}
`;
const StyledFilename = styled.div`
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
`;
const Thumbnail = styled.img`
  height: 48px;
  object-fit: cover;
  width: 48px;
  border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
`;
const RemoveLineButton = styled.button`
  background: none;
  border: none;
  cursor: pointer;
  height: 54px;
  line-height: 54px;
  margin: -15px;
  padding: 12px 0 0 0;
  width: 54px;
`;
const Input = styled.input<{readOnly?: boolean; isValid?: boolean}>`
  border-radius: 2px;
  border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
  height: 40px;
  line-height: 40px;
  padding: 0 5px 0 15px;
  width: 220px;

  ${props =>
    props.isValid === false &&
    css`
      border-color: ${(props: ThemedProps<void>) => props.theme.color.red100};
    `}
`;
const Errors = styled.div`
  align-items: flex-start;
  display: flex;
  justify-content: space-between;
`;
const StyledError = styled.div`
  align-items: center;
  color: ${(props: ThemedProps<void>) => props.theme.color.red100};
  font-size: 11px;
  line-height: 16px;
  margin: 5px 0 0;
  overflow-wrap: break-word;
  padding: 0;

  svg {
    float: left;
    margin: 0 5px 0 0;
  }
`;

type RowProps = {
  line: Line;
  locale: LocaleCode;
  channels: Channel[];
  locales: Locale[];
  onLineRemove: (line: Line) => void;
  onLineChange: (line: Line) => void;
  valuePerLocale: boolean;
  valuePerChannel: boolean;
};

type OptionsSelect2 = {
  [value: string]: string;
}

const getChannelsOptions = (channels: Channel[], locale: LocaleCode): OptionsSelect2 => {
  return channels.reduce((results: OptionsSelect2, channel: Channel) => {
    results[channel.code] = getChannelLabel(channel, locale);
    return results;
  }, {});
};

const getAllLocalesOptions = (locales: Locale[]): OptionsSelect2 => {
  return locales.reduce((results: OptionsSelect2, locale: Locale) => {
    results[locale.code] = locale.label;
    return results;
  }, {});
};

const getLocalesOptions = (channels: Channel[], locales: Locale[], line: Line): OptionsSelect2 => {
  if(null === line.channel){
    return getAllLocalesOptions(locales);
  }

  const channel = channels.find((channel: Channel) => channel.code === line.channel);
  if(undefined === channel){
    throw Error('Invalid channel in asset creation line: ' + line.channel);
  }

  return channel.locales.reduce((results: OptionsSelect2, locale: Locale) => {
    results[locale.code] = locale.label;
    return results;
  }, {});
};

const formatLocaleOption = (state: any): string => {
  if (!state.id) return state.text;

  const info = state.id.split('_');
  const flag = info[1].toLowerCase();
  const language = state.text;

  return `
<span class="flag-language">
  <i class="flag flag-${flag}"></i>
  <span class="language">${language}</span>
</span>
`;
};

const Error = ({message, ...props}: {message: string} & any) => (
  <StyledError {...props} aria-label={message}>
    <WarningIcon color={akeneoTheme.color.red100} size={16} />
    {message}
  </StyledError>
);

const Row = ({
  line,
  locale,
  channels,
  locales,
  onLineRemove,
  onLineChange,
  valuePerLocale,
  valuePerChannel,
}: RowProps) => {
  const status = getStatusFromLine(line, valuePerLocale, valuePerChannel);
  const errors = getAllErrorsOfLineByTarget(line);
  const channelsOptions = getChannelsOptions(channels, locale);
  const localesOptions = getLocalesOptions(channels, locales, line);

  return (
    <Container status={status}>
      <Cells>
        <Cell width={ColumnWidths.asset}>
          {null !== line.thumbnail && <Thumbnail src={line.thumbnail} title={line.filename} />}
        </Cell>
        <Cell width={ColumnWidths.filename}>
          <StyledFilename>{line.filename}</StyledFilename>
        </Cell>
        <Cell width={ColumnWidths.code}>
          <Input
            type="text"
            value={line.code}
            isValid={errors.code.length === 0}
            disabled={line.isAssetCreating || line.assetCreated}
            onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
              onLineChange({...line, code: event.target.value});
            }}
            aria-label={__('pim_asset_manager.asset.upload.list.code')}
          />
        </Cell>
        {valuePerChannel && (
          <Cell width={ColumnWidths.channel}>
            <Select2
              data={channelsOptions}
              value={null === line.channel ? '' : line.channel}
              multiple={false}
              readOnly={line.isAssetCreating || line.assetCreated}
              configuration={{
                allowClear: true,
              }}
              onChange={(value: string) => {
                onLineChange({...line, channel: value ? value : null});
              }}
              aria-label={__('pim_asset_manager.asset.upload.list.channel')}
            />
          </Cell>
        )}
        {valuePerLocale && (
          <Cell width={ColumnWidths.locale}>
            <Select2
              data={localesOptions}
              value={null === line.locale ? '' : line.locale}
              multiple={false}
              readOnly={line.isAssetCreating || line.assetCreated}
              configuration={{
                allowClear: true,
                formatResult: formatLocaleOption,
                formatSelection: formatLocaleOption,
              }}
              onChange={(value: string) => {
                onLineChange({...line, locale: value ? value : null});
              }}
              aria-label={__('pim_asset_manager.asset.upload.list.locale')}
            />
          </Cell>
        )}
        <Spacer />
        <Cell width={ColumnWidths.status}>
          <RowStatus status={status} progress={line.uploadProgress} />
        </Cell>
        <Cell width={ColumnWidths.remove}>
          <RemoveLineButton onClick={() => onLineRemove(line)} aria-label={__('pim_asset_manager.asset.upload.remove')}>
            <CrossIcon />
          </RemoveLineButton>
        </Cell>
      </Cells>
      <Errors>
        <Cell width={ColumnWidths.asset + ColumnWidths.filename}>
          {errors.all.map(error => (
            <Error key={error.message} message={error.message} />
          ))}
        </Cell>
        <Cell width={ColumnWidths.code}>
          {errors.code.map(error => (
            <Error key={error.message} message={error.message} />
          ))}
        </Cell>
        <Cell width={ColumnWidths.locale}>
          {errors.locale.map(error => (
            <Error key={error.message} message={error.message} />
          ))}
        </Cell>
        <Cell width={ColumnWidths.channel}>
          {errors.channel.map(error => (
            <Error key={error.message} message={error.message} />
          ))}
        </Cell>
        <Spacer />
      </Errors>
    </Container>
  );
};

export default Row;
