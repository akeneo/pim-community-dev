import React from 'react';
import {CloseIcon, IconButton, Table, TextInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

const RemoveCell = styled(Table.Cell)`
  width: 50px;
`;

type StringRowProps = {
  value: string;
  onChange: (value: string) => void;
  onRemove: () => void;
};

const StringRow = ({value, onChange}: StringRowProps) => {
  const translate = useTranslate();

  return (
    <Table.Row>
      <Table.Cell>
        <TextInput value={value} onChange={onChange} />
      </Table.Cell>
      <RemoveCell>
        <IconButton ghost="borderless" level="tertiary" title={translate('pim_common.remove')} icon={<CloseIcon />} />
      </RemoveCell>
    </Table.Row>
  );
};

export {StringRow};
