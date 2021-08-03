import React, {useEffect, useState} from 'react';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {AttributeOption} from '../model';
import {useAttributeContext} from '../contexts';
import {useAttributeOptionsListState} from '../hooks';
import {useSortedAttributeOptions} from '../hooks';
import AutoOptionSorting from './AutoOptionSorting';
import NewOptionPlaceholder from './NewOptionPlaceholder';
import {Button, CloseIcon, IconButton, RowIcon, Table} from 'akeneo-design-system';
import styled from 'styled-components';
import DeleteConfirmationModal from './DeleteConfirmationModal';

interface ListProps {
  selectAttributeOption: (selectedOptionId: number | null) => void;
  isNewOptionFormDisplayed: boolean;
  showNewOptionForm: (isDisplayed: boolean) => void;
  selectedOptionId: number | null;
  deleteAttributeOption: (attributeOptionId: number) => void;
  manuallySortAttributeOptions: (attributeOptions: AttributeOption[]) => void;
}

const AttributeOptionTable = ({
  selectAttributeOption,
  selectedOptionId,
  isNewOptionFormDisplayed,
  showNewOptionForm,
  deleteAttributeOption,
  manuallySortAttributeOptions,
}: ListProps) => {
  const {attributeOptions, extraData} = useAttributeOptionsListState();
  const translate = useTranslate();
  const attributeContext = useAttributeContext();
  const locale = useUserContext().get('catalogLocale');
  const {sortedAttributeOptions, setSortedAttributeOptions} = useSortedAttributeOptions(
    attributeOptions,
    attributeContext.autoSortOptions,
    manuallySortAttributeOptions
  );
  const [showDeleteConfirmationModal, setShowDeleteConfirmationModal] = useState<boolean>(false);
  const [showNewOptionPlaceholder, setShowNewOptionPlaceholder] = useState<boolean>(isNewOptionFormDisplayed);
  const [isDraggable, setIsDraggable] = useState<boolean>(attributeContext.autoSortOptions);

  useEffect(() => {
    if (selectedOptionId !== null) {
      setShowNewOptionPlaceholder(false);
    }
  }, [selectedOptionId]);

  useEffect(() => {
    attributeContext.autoSortOptions ? setIsDraggable(false) : setIsDraggable(true);
  }, [attributeContext.autoSortOptions]);

  const onSelectItem = (optionId: number) => {
    setShowNewOptionPlaceholder(false);
    selectAttributeOption(optionId);
    showNewOptionForm(false);
  };

  const displayNewOptionPlaceholder = () => {
    setShowNewOptionPlaceholder(true);
    selectAttributeOption(null);
    showNewOptionForm(true);
  };

  const cancelNewOption = () => {
    showNewOptionForm(false);
    setShowNewOptionPlaceholder(false);
    if (attributeOptions !== null && attributeOptions.length > 0) {
      selectAttributeOption(attributeOptions[0].id);
    }
  };

  const reorderAttributeOptions = (newIndices: number[]) => {
    setSortedAttributeOptions((rows: AttributeOption[]): AttributeOption[] => {
      const newSortedAttributeOptions = newIndices.map(index => rows[index]);
      if (typeof newSortedAttributeOptions[newSortedAttributeOptions.length - 1] === 'undefined') {
        newSortedAttributeOptions.pop();
      }

      manuallySortAttributeOptions(newSortedAttributeOptions);

      return newSortedAttributeOptions;
    });
  };

  return (
    <div className="AknSubsection AknAttributeOption-list">
      <div className="AknSubsection-title AknSubsection-title--glued tabsection-title">
        <span>{translate('pim_enrich.entity.attribute_option.module.edit.options_codes')}</span>
        <Button ghost level="tertiary" onClick={() => displayNewOptionPlaceholder()}>
          {translate('pim_enrich.entity.product.module.attribute.add_option')}
        </Button>
      </div>

      <AutoOptionSorting />

      <SpacedTable isDragAndDroppable={isDraggable} onReorder={newIndices => reorderAttributeOptions(newIndices)}>
        <Table.Header sticky={44}>
          {!isDraggable && <Table.HeaderCell>&nbsp;</Table.HeaderCell>}
          <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
          <Table.HeaderCell>{translate('pim_common.code')}</Table.HeaderCell>
          <Table.HeaderCell>&nbsp;</Table.HeaderCell>
          <Table.HeaderCell>&nbsp;</Table.HeaderCell>
        </Table.Header>
        <Table.Body>
          {sortedAttributeOptions !== null &&
            sortedAttributeOptions.map((attributeOption: AttributeOption, index: number) => {
              const deleteOption = () => {
                setShowDeleteConfirmationModal(false);
                deleteAttributeOption(attributeOption.id);
              };

              return (
                <TableRow
                  isDraggable={isDraggable}
                  isSelected={selectedOptionId === attributeOption.id}
                  onClick={() => onSelectItem(attributeOption.id)}
                  key={`${attributeOption.code}${index}`}
                >
                  {!isDraggable && (
                    <TableCellNoDraggable>
                      <HandleContainer>
                        <RowIcon size={16} />
                      </HandleContainer>
                    </TableCellNoDraggable>
                  )}
                  <TableCellLabel rowTitle={true}>
                    {attributeOption.optionValues[locale] && attributeOption.optionValues[locale].value
                      ? attributeOption.optionValues[locale].value
                      : `[${attributeOption.code}]`}
                  </TableCellLabel>
                  <Table.Cell>{attributeOption.code}</Table.Cell>
                  <Table.Cell>{extraData[attributeOption.code]}</Table.Cell>
                  <TableActionCell>
                    <IconButton
                      icon={<CloseIcon />}
                      onClick={(event: any) => {
                        event.preventDefault();
                        event.stopPropagation();
                        setShowDeleteConfirmationModal(true);
                      }}
                      title={translate('pim_common.delete')}
                      ghost="borderless"
                      level="tertiary"
                    />

                    {showDeleteConfirmationModal && (
                      <DeleteConfirmationModal
                        attributeOptionCode={attributeOption.code}
                        confirmDelete={deleteOption}
                        cancelDelete={() => setShowDeleteConfirmationModal(false)}
                      />
                    )}
                  </TableActionCell>
                </TableRow>
              );
            })}

          {showNewOptionPlaceholder && (
            <NewOptionPlaceholder cancelNewOption={cancelNewOption} isDraggable={isDraggable} />
          )}
        </Table.Body>
      </SpacedTable>
    </div>
  );
};

const SpacedTable = styled(Table)`
  th {
    padding-top: 15px;
  }
`;

const TableCellLabel = styled(Table.Cell)`
  width: 35%;
`;

const TableCellNoDraggable = styled(Table.Cell)`
  width: 40px;
`;

const HandleContainer = styled.div`
  cursor: grab;
  display: flex;
  align-items: center;
  justify-content: center;
`;

const TableRow = styled(Table.Row)<{isDraggable: boolean}>`
  td:first-child {
    color: ${({isDraggable}) => (isDraggable ? 'grey' : '#f0f1f3')};
  }
`;

const TableActionCell = styled(Table.ActionCell)`
  width: 50px;
`;

export default AttributeOptionTable;
