import * as React from 'react';
import {Button} from 'akeneoassetmanager/application/component/app/button';
import {Spacer} from 'akeneoassetmanager/application/component/app/spacer';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import Line from 'akeneoassetmanager/application/asset-upload/model/line';

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
const List = styled.table``;

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
        <tbody>
          {lines.map((line: Line) => (
            <tr key={line.id}>
              <td>{null !== line.thumbnail && <img width={100} src={line.thumbnail} />}</td>
              <td>{line.code}</td>
              <td>{line.uploadProgress}</td>
              <td>{line.file?.filePath}</td>
              <td>
                <span onClick={() => onLineRemove(line)}>remove</span>
              </td>
            </tr>
          ))}
        </tbody>
      </List>
    </>
  );
};

export default LineList;
