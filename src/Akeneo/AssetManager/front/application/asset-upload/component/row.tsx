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
import ReloadIcon from 'akeneoassetmanager/application/component/app/icon/reload';
import Channel from 'akeneoassetmanager/domain/model/channel';
import Select2, {Select2Options} from 'akeneoassetmanager/application/component/app/select2';
import Locale, {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {
  formatLocaleOption,
  getOptionsFromChannels,
  getOptionsFromLocales,
} from 'akeneoassetmanager/application/asset-upload/utils/select2';

const Container = styled.div<{status?: LineStatus; isReadOnly?: boolean}>`
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
  padding: 15px 0;

  ${props =>
    props.status === LineStatus.Invalid &&
    css`
      background: ${(props: ThemedProps<void>) => props.theme.color.red20};
      border-bottom: 1px solid #ffffff;
    `}

  ${props =>
    props.isReadOnly === true &&
    css`
      opacity: 0.3;
      user-select: none;
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
  padding: 7px 0 0 0;
  width: 54px;
`;
const RetryUploadButton = styled.button`
  background: none;
  border: none;
  cursor: pointer;
  height: 54px;
  line-height: 54px;
  margin: -15px;
  padding: 7px 0 0 0;
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

const Error = ({message, ...props}: {message: string} & any) => (
  <StyledError {...props} aria-label={message}>
    <WarningIcon color={akeneoTheme.color.red100} size={16} />
    {message}
  </StyledError>
);

type ChannelDropdownProps = {
  options: Select2Options;
  value: string | null;
  readOnly: boolean;
  onChange: (value: string) => void;
};
const ChannelDropdown = React.memo(({options, value, readOnly, onChange}: ChannelDropdownProps) => {
  return (
    <Select2
      data={options}
      value={null === value ? '' : value}
      multiple={false}
      readOnly={readOnly}
      configuration={{
        allowClear: true,
      }}
      onChange={onChange}
      aria-label={__('pim_asset_manager.asset.upload.list.channel')}
    />
  );
});

type LocaleDropdownProps = {
  options: Select2Options;
  value: string | null;
  readOnly: boolean;
  onChange: (value: string) => void;
};
const LocaleDropdown = React.memo(({options, value, readOnly, onChange}: LocaleDropdownProps) => {
  return (
    <Select2
      data={options}
      value={null === value ? '' : value}
      multiple={false}
      readOnly={readOnly}
      configuration={{
        allowClear: true,
        formatResult: formatLocaleOption,
        formatSelection: formatLocaleOption,
      }}
      onChange={onChange}
      aria-label={__('pim_asset_manager.asset.upload.list.locale')}
    />
  );
});

type RowProps = {
  line: Line;
  locale: LocaleCode;
  channels: Channel[];
  locales: Locale[];
  onLineRemove: (line: Line) => void;
  onLineChange: (line: Line) => void;
  onLineUploadRetry: (line: Line) => void;
  valuePerLocale: boolean;
  valuePerChannel: boolean;
};
const Row = React.memo(
  ({
    line,
    locale,
    channels,
    locales,
    onLineRemove,
    onLineChange,
    onLineUploadRetry,
    valuePerLocale,
    valuePerChannel,
  }: RowProps) => {
    const status = getStatusFromLine(line, valuePerLocale, valuePerChannel);
    const errors = getAllErrorsOfLineByTarget(line);
    const channelOptions = getOptionsFromChannels(channels, locale);
    const localeOptions = getOptionsFromLocales(channels, locales, line.channel);
    const isReadOnly = line.isAssetCreating || line.assetCreated;

    const handleCodeChange = React.useCallback(
      (event: React.ChangeEvent<HTMLInputElement>) => {
        onLineChange({...line, code: event.target.value});
      },
      [line]
    );

    const handleChannelChange = React.useCallback(
      (value: string) => {
        onLineChange({...line, channel: value ? value : null});
      },
      [line]
    );

    const handleLocaleChange = React.useCallback(
      (value: string) => {
        onLineChange({...line, locale: value ? value : null});
      },
      [line]
    );

    const handleRetryUpload = React.useCallback(() => {
      onLineUploadRetry(line);
    }, [onLineUploadRetry, line]);

    const handleLineRemove = React.useCallback(() => {
      onLineRemove(line);
    }, [line, onLineRemove]);

    return (
      <Container status={status} isReadOnly={isReadOnly}>
        <Cells>
          <Cell width={ColumnWidths.asset}>{null !== line.thumbnail && <Thumbnail src={line.thumbnail} alt="" />}</Cell>
          <Cell width={ColumnWidths.filename}>
            <StyledFilename>{line.filename}</StyledFilename>
          </Cell>
          <Cell className={'edit-asset-code-input'} width={ColumnWidths.code}>
            <Input
              type="text"
              value={line.code}
              isValid={errors.code.length === 0}
              disabled={isReadOnly}
              onChange={handleCodeChange}
              aria-label={__('pim_asset_manager.asset.upload.list.code')}
            />
          </Cell>
          {valuePerChannel && (
            <Cell width={ColumnWidths.channel}>
              <ChannelDropdown
                options={channelOptions}
                value={line.channel}
                readOnly={isReadOnly}
                onChange={handleChannelChange}
              />
            </Cell>
          )}
          {valuePerLocale && (
            <Cell width={ColumnWidths.locale}>
              <LocaleDropdown
                options={localeOptions}
                value={line.locale}
                readOnly={isReadOnly}
                onChange={handleLocaleChange}
              />
            </Cell>
          )}
          <Spacer />
          <Cell width={ColumnWidths.status}>
            <RowStatus status={status} progress={line.uploadProgress} />
          </Cell>
          <Cell width={ColumnWidths.retry}>
            {!isReadOnly && line.isFileUploadFailed && (
              <RetryUploadButton onClick={handleRetryUpload} aria-label={__('pim_asset_manager.asset.upload.retry')}>
                <ReloadIcon />
              </RetryUploadButton>
            )}
          </Cell>
          <Cell width={ColumnWidths.remove}>
            {!isReadOnly && (
              <RemoveLineButton onClick={handleLineRemove} aria-label={__('pim_asset_manager.asset.upload.remove')}>
                <CrossIcon />
              </RemoveLineButton>
            )}
          </Cell>
        </Cells>
        <Errors>
          <Cell width={ColumnWidths.asset + ColumnWidths.filename}>
            {errors.common.map(error => (
              <Error key={error.message} message={error.message} />
            ))}
          </Cell>
          <Cell width={ColumnWidths.code}>
            {errors.code.map(error => (
              <Error key={error.message} message={error.message} />
            ))}
          </Cell>
          <Cell width={ColumnWidths.channel}>
            {errors.channel.map(error => (
              <Error key={error.message} message={error.message} />
            ))}
          </Cell>
          <Cell width={ColumnWidths.locale}>
            {errors.locale.map(error => (
              <Error key={error.message} message={error.message} />
            ))}
          </Cell>
          <Spacer />
        </Errors>
      </Container>
    );
  }
);

export default Row;
