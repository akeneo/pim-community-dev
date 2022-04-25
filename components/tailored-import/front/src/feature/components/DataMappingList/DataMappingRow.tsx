import React, {SyntheticEvent} from 'react';
import styled from 'styled-components';
import {CloseIcon, IconButton, Table, useBooleanState, Pill} from 'akeneo-design-system';
import {DeleteModal, useTranslate} from '@akeneo-pim-community/shared';
import {Column, DataMapping, generateColumnName} from '../../models';

type DataMappingRowProps = {
  dataMapping: DataMapping;
  columns: Column[];
  hasError: boolean;
  isSelected: boolean;
  isIdentifierDataMapping: boolean;
  onSelect: (uuid: string) => void;
  onRemove: (uuid: string) => void;
};

const DataMappingCodeCell = styled(Table.Cell)`
  width: 50%;
`;

const Spacer = styled.div`
  flex: 1;
`;

const RemoveCell = styled(Table.ActionCell)`
  width: 50px;
`;

const DataMappingRow = ({
  dataMapping,
  columns,
  hasError,
  isSelected,
  isIdentifierDataMapping,
  onRemove,
  onSelect,
}: DataMappingRowProps) => {
  const translate = useTranslate();
  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState();

  const sources = dataMapping.sources.map(uuid => {
    const column = columns.find(column => uuid === column.uuid);

    return column ? generateColumnName(column.index, column.label) : '';
  });

  const handleRemove = (event: SyntheticEvent) => {
    event.stopPropagation();
    openDeleteModal();
  };

  const handleConfirmRemove = () => {
    onRemove(dataMapping.uuid);
  };

  return (
    <>
      <Table.Row onClick={() => onSelect(dataMapping.uuid)} isSelected={isSelected}>
        <DataMappingCodeCell>{dataMapping.target.code}</DataMappingCodeCell>
        <Table.Cell>
          {sources.length === 0
            ? translate('akeneo.tailored_import.data_mapping_list.no_sources')
            : `${translate('akeneo.tailored_import.data_mapping.sources.title')}: ${sources.join(' ')}`}
          <Spacer />
          {hasError && <Pill level="danger" />}
        </Table.Cell>
        {isIdentifierDataMapping ? (
          <Table.Cell />
        ) : (
          <RemoveCell>
            <IconButton
              ghost="borderless"
              level="tertiary"
              icon={<CloseIcon />}
              title={translate('pim_common.remove')}
              onClick={handleRemove}
            />
          </RemoveCell>
        )}
      </Table.Row>
      {isDeleteModalOpen && (
        <DeleteModal
          title={translate('akeneo.tailored_import.data_mapping_list.title')}
          confirmButtonLabel={translate('pim_common.confirm')}
          onConfirm={handleConfirmRemove}
          onCancel={closeDeleteModal}
        >
          {translate('akeneo.tailored_import.data_mapping_list.remove')}
        </DeleteModal>
      )}
    </>
  );
};

export {DataMappingRow};
