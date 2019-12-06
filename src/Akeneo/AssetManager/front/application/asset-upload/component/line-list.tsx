import * as React from 'react';
import {Button} from 'akeneoassetmanager/application/component/app/button';
import {Spacer} from 'akeneoassetmanager/application/component/app/spacer';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import Line from 'akeneoassetmanager/application/asset-upload/model/line';
import Row from 'akeneoassetmanager/application/asset-upload/component/row';

const Header = styled.div`
  align-items: center;
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey140};
  display: flex;
  padding-bottom: 7px;
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
const List = styled.table`
  border-collapse: collapse;
  width: 100%;
`;
const ListHeader = styled.thead`
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey120};
  margin-top: 10px;
`;
const ListColumnHeader = styled.th<{width?: number}>`
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140};
  height: 44px;
  line-height: 44px;
  padding-left: 15px;
  text-align: left;
  width: ${(props: ThemedProps<{width?: number}>) => (props.width ? props.width : 'auto')};
`;

type LineListProps = {
  lines: Line[];
  onLineRemove: (line: Line) => void;
  localizable?: boolean;
  scopable?: boolean;
};

const LineList = ({lines, onLineRemove, localizable = true, scopable = true}: LineListProps) => {
  return (
    <>
      <Header>
        <LineCount>{__('pim_asset_manager.asset.upload.line_count', {count: lines.length}, lines.length)}</LineCount>
        <Spacer />
        <ActionButton color="outline">{__('pim_asset_manager.asset.upload.remove_all')}</ActionButton>
      </Header>
      <List>
        <ListHeader>
          <tr>
            <ListColumnHeader width={78}>{__('pim_asset_manager.asset.upload.list.asset')}</ListColumnHeader>
            <ListColumnHeader>{__('pim_asset_manager.asset.upload.list.filename')}</ListColumnHeader>
            <ListColumnHeader>{__('pim_asset_manager.asset.upload.list.code')}</ListColumnHeader>
            {localizable && <ListColumnHeader>{__('pim_asset_manager.asset.upload.list.locale')}</ListColumnHeader>}
            {scopable && <ListColumnHeader>{__('pim_asset_manager.asset.upload.list.channel')}</ListColumnHeader>}
            <ListColumnHeader width={0}>{__('pim_asset_manager.asset.upload.list.status')}</ListColumnHeader>
            <ListColumnHeader width={0} />
          </tr>
        </ListHeader>
        <tbody>
          {lines.map((line: Line) => (
            <Row key={line.id} line={line} onLineRemove={onLineRemove} localizable={localizable} scopable={scopable} />
          ))}
        </tbody>
      </List>
    </>
  );
};

export default LineList;
