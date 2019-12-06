import * as React from 'react';
import Line from 'akeneoassetmanager/application/asset-upload/model/line';
import CrossIcon from 'akeneoassetmanager/application/component/app/icon/close';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import RowStatus from 'akeneoassetmanager/application/asset-upload/component/row-status';

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
  localizable: boolean;
  scopable: boolean;
};

const Row = ({line, onLineRemove, onLineChange, localizable = true, scopable = true}: RowProps) => {
  return (
    <Container>
      <Cell>{null !== line.thumbnail && <Thumbnail src={line.thumbnail} />}</Cell>
      <Cell>{line.filename}</Cell>
      <Cell>
        <Input type="text" value={line.code} />
      </Cell>
      {localizable && (
        <Cell>
          <Input type="text" value={line.locale} />
        </Cell>
      )}
      {scopable && (
        <Cell>
          <Input type="text" value={line.channel} />
        </Cell>
      )}
      <Cell>
        <RowStatus status={line.status} progress={line.uploadProgress} />
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
