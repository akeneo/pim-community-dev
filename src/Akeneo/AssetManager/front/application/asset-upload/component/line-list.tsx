import * as React from 'react';
import {Button} from 'akeneoassetmanager/application/component/app/button';
import {Spacer} from 'akeneoassetmanager/application/component/app/spacer';
import __ from 'akeneoassetmanager/tools/translator';
import styled, {css} from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import Line from 'akeneoassetmanager/application/asset-upload/model/line';
import Row from 'akeneoassetmanager/application/asset-upload/component/row';
import Locale, {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import Channel from 'akeneoassetmanager/domain/model/channel';
import {AssetsIllustration} from 'akeneo-design-system';

export const ColumnWidths = {
  asset: 78,
  filename: 165,
  code: 250,
  locale: 250,
  channel: 250,
  status: 140,
  retry: 54,
  remove: 54,
};

const Header = styled.div`
  align-items: center;
  background: ${(props: ThemedProps<void>) => props.theme.color.white};
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey140};
  display: flex;
  height: 40px;
  padding-bottom: 7px;
  position: sticky;
  top: 93px;
  z-index: 2;
`;
const LineCount = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140};
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.big};
  font-weight: normal;
  text-transform: uppercase;
`;
const ActionButton = styled(Button)`
  margin-left: 10px;
`;
const List = styled.div`
  border-collapse: collapse;
  width: 100%;
`;
const ListHeader = styled.div`
  align-items: center;
  background: ${(props: ThemedProps<void>) => props.theme.color.white};
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey120};
  display: flex;
  justify-content: space-between;
  margin-top: 10px;
  position: sticky;
  top: 133px;
  z-index: 1;
`;
const ListColumnHeader = styled.div<{width?: number}>`
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140};
  flex-grow: 0;
  flex-shrink: 0;
  height: 44px;
  line-height: 44px;
  padding-left: 15px;
  text-align: left;
  white-space: nowrap;

  ${props =>
    props.width !== undefined &&
    css`
      width: ${props.width}px;
    `}
`;
const Placeholder = styled.div`
  align-items: center;
  display: flex;
  flex-direction: column;
  padding: 60px 0;
`;
const PlaceholderHelper = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140};
  font-size: 30px;
  line-height: 30px;
  margin-top: 7px;
`;

type LineListProps = {
  lines: Line[];
  locale: LocaleCode;
  channels: Channel[];
  locales: Locale[];
  onLineRemove: (line: Line) => void;
  onLineRemoveAll: () => void;
  onLineChange: (line: Line) => void;
  onLineUploadRetry: (line: Line) => void;
  valuePerLocale: boolean;
  valuePerChannel: boolean;
};

const LineList = ({
  lines,
  locale,
  channels,
  locales,
  onLineRemove,
  onLineRemoveAll,
  onLineChange,
  onLineUploadRetry,
  valuePerLocale,
  valuePerChannel,
}: LineListProps) => {
  return (
    <>
      <Header>
        <LineCount>{__('pim_asset_manager.asset.upload.line_count', {count: lines.length}, lines.length)}</LineCount>
        <Spacer />
        <ActionButton color="outline" onClick={onLineRemoveAll}>
          {__('pim_asset_manager.asset.upload.remove_all')}
        </ActionButton>
      </Header>
      <List>
        <ListHeader>
          <ListColumnHeader width={ColumnWidths.asset}>
            {__('pim_asset_manager.asset.upload.list.asset')}
          </ListColumnHeader>
          <ListColumnHeader width={ColumnWidths.filename}>
            {__('pim_asset_manager.asset.upload.list.filename')}
          </ListColumnHeader>
          <ListColumnHeader className={'edit-asset-code-label'} width={ColumnWidths.code}>
            {__('pim_asset_manager.asset.upload.list.code')}
          </ListColumnHeader>
          {valuePerChannel && (
            <ListColumnHeader width={ColumnWidths.channel}>
              {__('pim_asset_manager.asset.upload.list.channel')}
            </ListColumnHeader>
          )}
          {valuePerLocale && (
            <ListColumnHeader width={ColumnWidths.locale}>
              {__('pim_asset_manager.asset.upload.list.locale')}
            </ListColumnHeader>
          )}
          <Spacer />
          <ListColumnHeader width={ColumnWidths.status}>
            {__('pim_asset_manager.asset.upload.list.status')}
          </ListColumnHeader>
          <ListColumnHeader width={ColumnWidths.retry} />
          <ListColumnHeader width={ColumnWidths.remove} />
        </ListHeader>
        <div aria-label={__('pim_asset_manager.asset.upload.lines')}>
          {lines.map((line: Line) => (
            <Row
              key={line.id}
              line={line}
              locale={locale}
              channels={channels}
              locales={locales}
              onLineChange={onLineChange}
              onLineRemove={onLineRemove}
              onLineUploadRetry={onLineUploadRetry}
              valuePerLocale={valuePerLocale}
              valuePerChannel={valuePerChannel}
            />
          ))}
        </div>
      </List>
      {lines.length === 0 && (
        <Placeholder>
          <AssetsIllustration />
          <PlaceholderHelper>{__('pim_asset_manager.asset.upload.will_appear_here')}</PlaceholderHelper>
        </Placeholder>
      )}
    </>
  );
};

export default LineList;
