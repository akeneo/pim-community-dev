import React, {useEffect, useState} from 'react';

import {useTranslate} from '@akeneo-pim-community/shared';
import {AttributeOption} from '../model';
import {useAttributeContext} from '../contexts';
import {useAttributeOptionsListState} from '../hooks/useAttributeOptionsListState';
import {useSortedAttributeOptions} from '../hooks/useSortedAttributeOptions';
import AutoOptionSorting from './AutoOptionSorting';
import NewOptionPlaceholder from './NewOptionPlaceholder';
import {Button, Table} from "akeneo-design-system";
import styled from "styled-components";
import AttributeOptionTableRow, {DragItem} from "./AttributeOptionTableRow";

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
  const {sortedAttributeOptions, moveAttributeOption, validateMoveAttributeOption} = useSortedAttributeOptions(
    attributeOptions,
    attributeContext.autoSortOptions,
    manuallySortAttributeOptions
  );
  const [showNewOptionPlaceholder, setShowNewOptionPlaceholder] = useState<boolean>(isNewOptionFormDisplayed);
  const [dragItem, setDragItem] = useState<DragItem | null>(null);
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

  return (
    <div className="AknSubsection AknAttributeOption-list">
      <div className="AknSubsection-title AknSubsection-title--glued tabsection-title">
        <span>{translate('pim_enrich.entity.attribute_option.module.edit.options_codes')}</span>
        <Button
          ghost
          level="tertiary"
          onClick={() => displayNewOptionPlaceholder()}
        >
          {translate('pim_enrich.entity.product.module.attribute.add_option')}
        </Button>
      </div>

      <AutoOptionSorting />

      <SpacedTable
        isDragAndDroppable={true}
        onReorder={newIndices => {
          console.log(newIndices);
        }}
      >
        <Table.Header sticky={44}>
          <Table.HeaderCell>{translate('pim_common.code')}</Table.HeaderCell>
          <Table.HeaderCell>&nbsp;</Table.HeaderCell>
          <Table.HeaderCell>&nbsp;</Table.HeaderCell>
        </Table.Header>
        <Table.Body>
          {sortedAttributeOptions !== null &&
          sortedAttributeOptions.map((attributeOption: AttributeOption, index: number) => {
            return (
              <AttributeOptionTableRow
                key={attributeOption.code}
                data={attributeOption}
                selectAttributeOption={onSelectItem}
                isSelected={selectedOptionId === attributeOption.id}
                deleteAttributeOption={deleteAttributeOption}
                isDraggable={isDraggable}
                moveAttributeOption={moveAttributeOption}
                validateMoveAttributeOption={validateMoveAttributeOption}
                dragItem={dragItem}
                setDragItem={setDragItem}
                index={index}
              >
                {extraData[attributeOption.code]}
              </AttributeOptionTableRow>
            );
          })}

          {showNewOptionPlaceholder && <NewOptionPlaceholder cancelNewOption={cancelNewOption} />}
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

export default AttributeOptionTable;
