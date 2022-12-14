import React, {SyntheticEvent} from 'react';
import styled from 'styled-components';
import {CloseIcon, IconButton, Table, useBooleanState, Pill} from 'akeneo-design-system';
import {DeleteModal, useTranslate} from '@akeneo-pim-community/shared';
import {Column, DataMapping, generateColumnName, isAttributeDataMapping} from '../../models';
import {AttributeLabelCell} from './AttributeLabelCell';

type DataMappingRowProps = {
  dataMapping: DataMapping;
  columns: Column[];
  hasError: boolean;
  isSelected: boolean;
  isIdentifierDataMapping: boolean;
  onSelect: (uuid: string) => void;
  onRemove: (uuid: string) => void;
};

const PropertyLabelCell = styled(Table.Cell)`
  width: 50%;
`;

const Spacer = styled.div`
  flex: 1;
`;

const DisplayedSources = styled.span`
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
`;

const DisplayedProperty = styled.span`
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
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

  const joinedSourcesList =
    0 === sources.length ? translate('akeneo.tailored_import.data_mapping_list.no_sources') : sources.join(', ');

  return (
    <>
      <Table.Row onClick={() => onSelect(dataMapping.uuid)} isSelected={isSelected}>
        {isAttributeDataMapping(dataMapping) ? (
          <AttributeLabelCell attributeCode={dataMapping.target.code} />
        ) : (
          <PropertyLabelCell rowTitle={true}>
            <DisplayedProperty title={translate(`pim_common.${dataMapping.target.code}`)}>
              {translate(`pim_common.${dataMapping.target.code}`)}
            </DisplayedProperty>
          </PropertyLabelCell>
        )}
        <Table.Cell>
          <DisplayedSources title={joinedSourcesList}>{joinedSourcesList}</DisplayedSources>
          <Spacer />
          {hasError && <Pill level="danger" />}
        </Table.Cell>
        {isIdentifierDataMapping ? (
          <Table.Cell />
        ) : (
          <Table.ActionCell>
            <IconButton
              ghost="borderless"
              level="tertiary"
              icon={<CloseIcon />}
              title={translate('pim_common.remove')}
              onClick={handleRemove}
            />
          </Table.ActionCell>
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
