import React from 'react';
import styled, {css} from 'styled-components';
import {Spacer} from 'akeneoassetmanager/application/component/app/spacer';
import Line from 'akeneoassetmanager/application/asset-upload/model/line';
import Row from 'akeneoassetmanager/application/asset-upload/component/row';
import Locale, {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import Channel from 'akeneoassetmanager/domain/model/channel';
import {AssetsIllustration, Button, getColor, getFontSize} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

export const ColumnWidths = {
  asset: 78,
  filename: 165,
  code: 250,
  locale: 250,
  channel: 250,
  status: 140,
  actions: 108,
};

const Header = styled.div`
  align-items: center;
  background: ${getColor('white')};
  border-bottom: 1px solid ${getColor('grey', 140)};
  display: flex;
  height: 40px;
  padding-bottom: 7px;
  position: sticky;
  top: 93px;
  z-index: 2;
`;
const LineCount = styled.div`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('big')};
  font-weight: normal;
  text-transform: uppercase;
`;
const List = styled.div`
  border-collapse: collapse;
  width: 100%;
`;
const ListHeader = styled.div`
  align-items: center;
  background: ${getColor('white')};
  border-bottom: 1px solid ${getColor('grey', 120)};
  display: flex;
  justify-content: space-between;
  margin-top: 10px;
  position: sticky;
  top: 133px;
  z-index: 1;
`;
const ListColumnHeader = styled.div<{width?: number}>`
  color: ${getColor('grey', 140)};
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
  color: ${getColor('grey', 140)};
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
  const translate = useTranslate();

  return (
    <>
      <Header>
        <LineCount>
          {translate('pim_asset_manager.asset.upload.line_count', {count: lines.length}, lines.length)}
        </LineCount>
        <Spacer />
        <Button ghost={true} level="tertiary" onClick={onLineRemoveAll}>
          {translate('pim_asset_manager.asset.upload.remove_all')}
        </Button>
      </Header>
      <List>
        <ListHeader>
          <ListColumnHeader width={ColumnWidths.asset}>
            {translate('pim_asset_manager.asset.upload.list.asset')}
          </ListColumnHeader>
          <ListColumnHeader width={ColumnWidths.filename}>
            {translate('pim_asset_manager.asset.upload.list.filename')}
          </ListColumnHeader>
          <ListColumnHeader className={'edit-asset-code-label'} width={ColumnWidths.code}>
            {translate('pim_asset_manager.asset.upload.list.code')}
          </ListColumnHeader>
          {valuePerChannel && (
            <ListColumnHeader width={ColumnWidths.channel}>
              {translate('pim_asset_manager.asset.upload.list.channel')}
            </ListColumnHeader>
          )}
          {valuePerLocale && (
            <ListColumnHeader width={ColumnWidths.locale}>
              {translate('pim_asset_manager.asset.upload.list.locale')}
            </ListColumnHeader>
          )}
          <Spacer />
          <ListColumnHeader width={ColumnWidths.status}>
            {translate('pim_asset_manager.asset.upload.list.status')}
          </ListColumnHeader>
          <ListColumnHeader width={ColumnWidths.actions} />
        </ListHeader>
        <div aria-label={translate('pim_asset_manager.asset.upload.lines')}>
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
          <PlaceholderHelper>{translate('pim_asset_manager.asset.upload.will_appear_here')}</PlaceholderHelper>
        </Placeholder>
      )}
    </>
  );
};

export default LineList;
