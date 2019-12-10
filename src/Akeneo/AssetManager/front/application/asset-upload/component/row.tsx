import * as React from 'react';
import Line from 'akeneoassetmanager/application/asset-upload/model/line';
import CrossIcon from 'akeneoassetmanager/application/component/app/icon/close';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import RowStatus from 'akeneoassetmanager/application/asset-upload/component/row-status';
import {getStatusFromLine} from 'akeneoassetmanager/application/asset-upload/utils/utils';

const Container = styled.tr`
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
`;
const Cell = styled.td`
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
const Input = styled.input`
  border-radius: 2px;
  border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
  height: 40px;
  line-height: 40px;
  padding: 0 0 0 15px;
  width: 220px;
`;

type RowProps = {
  line: Line;
  onLineRemove: (line: Line) => void;
  onLineChange: (line: Line) => void;
  valuePerLocale: boolean;
  valuePerChannel: boolean;
};

const Row = ({line, onLineRemove, onLineChange, valuePerLocale = true, valuePerChannel = true}: RowProps) => {
  const status = getStatusFromLine(line, valuePerLocale, valuePerChannel);

  return (
    <Container>
      <Cell>{null !== line.thumbnail && <Thumbnail src={line.thumbnail} />}</Cell>
      <Cell>{line.filename}</Cell>
      <Cell>
        <Input
          type="text"
          value={line.code}
          onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
            onLineChange({...line, code: event.target.value});
          }}
        />
      </Cell>
      {valuePerLocale && (
        <Cell>
          <Input type="text" value={null === line.locale ? '' : line.locale} />
        </Cell>
      )}
      {valuePerChannel && (
        <Cell>
          <Input type="text" value={null === line.channel ? '' : line.channel} />
        </Cell>
      )}
      <Cell>
        <RowStatus status={status} progress={line.uploadProgress} />
      </Cell>
      <Cell>
        <RemoveLineButton onClick={() => onLineRemove(line)}>
          <CrossIcon />
        </RemoveLineButton>
      </Cell>
    </Container>
  );
};

export default Row;
