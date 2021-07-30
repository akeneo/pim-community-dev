import React, {useRef} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useScrollIntoView} from '../hooks/useScrollIntoView';
import {CloseIcon, IconButton, Table} from 'akeneo-design-system';
import styled from 'styled-components';

interface newOptionPlaceholderProps {
  cancelNewOption: () => void;
}

const NewOptionPlaceholder = ({cancelNewOption}: newOptionPlaceholderProps) => {
  const translate = useTranslate();
  const placeholderRef = useRef<HTMLDivElement>(null);

  useScrollIntoView(placeholderRef);

  return (
    <Table.Row isSelected={true} draggable={false}>
      <Table.Cell rowTitle={true}>
        {translate('pim_enrich.entity.attribute_option.module.edit.new_option_code')}
      </Table.Cell>
      <Table.Cell>&nbsp;</Table.Cell>
      <TableActionCell>
        <IconButton
          icon={<CloseIcon />}
          onClick={() => cancelNewOption()}
          title={translate('pim_common.delete')}
          ghost="borderless"
          level="tertiary"
        />
      </TableActionCell>
    </Table.Row>
  );
};

const TableActionCell = styled(Table.ActionCell)`
  width: 50px;
`;

export default NewOptionPlaceholder;
