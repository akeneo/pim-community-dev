import React from 'react';
import {CloseIcon, IconButton, Table, TextInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {ConcatElement} from '../../../../models';

const RemoveCell = styled(Table.Cell)`
  width: 50px;
`;

type StringRowProps = {
  element: ConcatElement;
  onConcatElementChange: (element: ConcatElement) => void;
  onConcatElementRemove: (elementUuid: string) => void;
};

const StringRow = ({element, onConcatElementChange, onConcatElementRemove, ...rest}: StringRowProps) => {
  const translate = useTranslate();

  const handleChange = (value: string) => onConcatElementChange({...element, value});
  const handleRemove = () => onConcatElementRemove(element.uuid);

  return (
    <Table.Row {...rest}>
      <Table.Cell>
        <TextInput value={element.value} onChange={handleChange} />
      </Table.Cell>
      <RemoveCell>
        <IconButton
          ghost="borderless"
          level="tertiary"
          title={translate('pim_common.remove')}
          icon={<CloseIcon />}
          onClick={handleRemove}
        />
      </RemoveCell>
    </Table.Row>
  );
};

export {StringRow};
