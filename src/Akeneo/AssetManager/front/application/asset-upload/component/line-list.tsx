import * as React from 'react';
import {Button} from 'akeneoassetmanager/application/component/app/button';
import {Spacer} from 'akeneoassetmanager/application/component/app/spacer';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import {akeneoTheme, ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import Line, {LineStatus} from 'akeneoassetmanager/application/asset-upload/model/line';
import CrossIcon from 'akeneoassetmanager/application/component/app/icon/close';

type LineListProps = {
  lines: Line[];
  onLineRemove: (line: Line) => void;
};

const Header = styled.div`
  display: flex;
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey140};
  padding-bottom: 7px;
  align-items: center;
`;
const AssetCount = styled.div`
  text-transform: uppercase;
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140};
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.big};
  font-weight: normal;
`;
const ActionButton = styled(Button)`
  margin-left: 10px;
`;
const List = styled.table`
  border-collapse: collapse;
  width: 100%;
`;
const ListHeader = styled.thead`
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey120};
  margin-top: 10px;
`;
const ListColumnHeader = styled.th<{width?: number}>`
  line-height: 44px;
  height: 44px;
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140};
  width: ${(props: ThemedProps<{width?: number}>) => (props.width ? props.width : 'auto')};
  padding-left: 15px;
  text-align: left;
`;
const ListLine = styled.tr`
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
`;
const ListCell = styled.td`
  padding: 15px;
`;
const Thumbnail = styled.img`
  height: 48px;
  object-fit: cover;
  width: 48px;
  border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
`;
const RemoveLineButton = styled.button`
  border: none;
  background: none;
  cursor: pointer;
`;
const StatusLabel = styled.span`
  color: 1px solid ${(props: ThemedProps<{color: string}>) => props.color};
  border: 1px solid ${(props: ThemedProps<{color: string}>) => props.color};
  text-transform: uppercase;
  border-radius: 2px;
  padding: 0 4px;
  font-size: 11px;
`;
const Input = styled.input`
  border-radius: 2px;
  border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
  height: 40px;
  line-height: 40px;
  padding: 0 0 0 15px;
`;

const renderStatus = (status: LineStatus, progress: number | null) => {
  switch (status) {
    case LineStatus.WaitingForUpload:
    case LineStatus.Incomplete:
      return (
        <StatusLabel color={akeneoTheme.color.grey100}>
          {__('pim_asset_manager.asset.upload.status.' + status)}
        </StatusLabel>
      );
    case LineStatus.Ready:
      return (
        <StatusLabel color={akeneoTheme.color.green100}>
          {__('pim_asset_manager.asset.upload.status.' + status)}
        </StatusLabel>
      );
    case LineStatus.Invalid:
      return (
        <StatusLabel color={akeneoTheme.color.red100}>
          {__('pim_asset_manager.asset.upload.status.' + status)}
        </StatusLabel>
      );
    case LineStatus.Uploaded:
      return (
        <StatusLabel color={akeneoTheme.color.blue100}>
          {__('pim_asset_manager.asset.upload.status.' + status)}
        </StatusLabel>
      );
    case LineStatus.UploadInProgress:
      return <span>{progress}%</span>;
    default:
      throw Error('unsupported line status');
  }
};

const LineList = ({lines, onLineRemove}: LineListProps) => {
  return (
    <>
      <Header>
        <AssetCount>{__('pim_asset_manager.asset.upload.asset_count', {count: lines.length}, lines.length)}</AssetCount>
        <Spacer />
        <ActionButton color="outline">{__('pim_asset_manager.asset.upload.add_new')}</ActionButton>
        <ActionButton color="outline">{__('pim_asset_manager.asset.upload.remove_all')}</ActionButton>
      </Header>
      <List>
        <ListHeader>
          <tr>
            <ListColumnHeader width={78}>{__('pim_asset_manager.asset.upload.list.asset')}</ListColumnHeader>
            <ListColumnHeader>{__('pim_asset_manager.asset.upload.list.filename')}</ListColumnHeader>
            <ListColumnHeader>{__('pim_asset_manager.asset.upload.list.code')}</ListColumnHeader>
            <ListColumnHeader>{__('pim_asset_manager.asset.upload.list.status')}</ListColumnHeader>
            <ListColumnHeader />
          </tr>
        </ListHeader>
        <tbody>
          {lines.map((line: Line) => (
            <ListLine key={line.id}>
              <ListCell>{null !== line.thumbnail && <Thumbnail src={line.thumbnail} />}</ListCell>
              <ListCell>{line.filename}</ListCell>
              <ListCell>
                <Input type="text" value={line.code} />
              </ListCell>
              <ListCell>{renderStatus(line.status, line.uploadProgress)}</ListCell>
              <ListCell>
                <RemoveLineButton onClick={() => onLineRemove(line)}>
                  <CrossIcon />
                </RemoveLineButton>
              </ListCell>
            </ListLine>
          ))}
        </tbody>
      </List>
    </>
  );
};

export default LineList;
