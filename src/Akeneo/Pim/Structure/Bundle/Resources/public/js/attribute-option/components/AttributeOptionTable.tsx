import React, {useEffect, useState} from 'react';
import {LocaleCode, SearchBar, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {AttributeOption, OptionValue} from '../model';
import {useAttributeContext} from '../contexts';
import {useAttributeOptionsListState} from '../hooks';
import {useSortedAttributeOptions} from '../hooks';
import AutoOptionSorting from './AutoOptionSorting';
import NewOptionPlaceholder from './NewOptionPlaceholder';
import {AkeneoThemedProps, Button, CloseIcon, getColor, IconButton, RowIcon, Table} from 'akeneo-design-system';
import styled from 'styled-components';
import DeleteConfirmationModal from './DeleteConfirmationModal';
import NoResultOnSearch from './NoResultOnSearch';

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
  const translate = useTranslate();
  const locale = useUserContext().get('catalogLocale');
  const attributeContext = useAttributeContext();

  const {attributeOptions, extraData} = useAttributeOptionsListState();
  const {sortedAttributeOptions, setSortedAttributeOptions} = useSortedAttributeOptions(
    attributeOptions,
    attributeContext.autoSortOptions
  );
  const [showDeleteConfirmationModal, setShowDeleteConfirmationModal] = useState<boolean>(false);
  const [showNewOptionPlaceholder, setShowNewOptionPlaceholder] = useState<boolean>(isNewOptionFormDisplayed);
  const [isDraggable, setIsDraggable] = useState<boolean>(attributeContext.autoSortOptions);
  const [searchValue, setSearchValue] = useState('');
  const [autoSortingReadOnly, setAutoSortingReadOnly] = useState<boolean>(false);

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
      const newSortedAttributeOptions = newIndices.map(index => rows[index]).filter(index => index !== undefined);

      manuallySortAttributeOptions(newSortedAttributeOptions);

      return newSortedAttributeOptions;
    });
  };

  const filterOnLabelOrCode =
    (searchValue: string, locale: LocaleCode) =>
    (entity: {code: string; optionValues: OptionValue}): boolean =>
      -1 !== entity.code.toLowerCase().indexOf(searchValue.toLowerCase()) ||
      (undefined !== entity.optionValues[locale] &&
        -1 !== entity.optionValues[locale].value.toLowerCase().indexOf(searchValue.toLowerCase()));

  const filteredAttributeOptions =
    null === sortedAttributeOptions ? null : sortedAttributeOptions.filter(filterOnLabelOrCode(searchValue, locale));

  const attributeOptionsCount = null === attributeOptions ? 0 : attributeOptions.length;

  const filteredAttributeOptionsCount = null === filteredAttributeOptions ? 0 : filteredAttributeOptions.length;

  const onSearchChange = (searchValue: string) => {
    if (searchValue) {
      setIsDraggable(false);
      setAutoSortingReadOnly(true);
    } else {
      if (!attributeContext.autoSortOptions) {
        setIsDraggable(true);
      }
      setAutoSortingReadOnly(false);
    }
    setSearchValue(searchValue);
  };

  return (
    <div className="AknSubsection AknAttributeOption-list">
      <div className="AknSubsection-title AknSubsection-title--glued tabsection-title">
        <span>{translate('pim_enrich.entity.attribute_option.module.edit.options_codes')}</span>
        <Button
          ghost
          level="tertiary"
          onClick={() => displayNewOptionPlaceholder()}
          role="add-new-attribute-option-button"
        >
          {translate('pim_enrich.entity.product.module.attribute.add_option')}
        </Button>
      </div>

      <SearchBar
        placeholder={translate('pim_enrich.entity.attribute_option.module.edit.search.placeholder')}
        count={filteredAttributeOptionsCount}
        searchValue={searchValue}
        onSearchChange={onSearchChange}
      />

      <div role="attribute-options-list">
        {filteredAttributeOptionsCount === 0 && attributeOptionsCount > 0 && <NoResultOnSearch />}

        {filteredAttributeOptionsCount > 0 && filteredAttributeOptions !== null && (
          <>
            <AutoOptionSorting readOnly={autoSortingReadOnly} />

            <SpacedTable isDragAndDroppable={isDraggable} onReorder={newIndices => reorderAttributeOptions(newIndices)}>
              <Table.Header sticky={44}>
                {!isDraggable && <Table.HeaderCell>&nbsp;</Table.HeaderCell>}
                <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
                <Table.HeaderCell>{translate('pim_common.code')}</Table.HeaderCell>
                <Table.HeaderCell>&nbsp;</Table.HeaderCell>
                <Table.HeaderCell>&nbsp;</Table.HeaderCell>
              </Table.Header>
              <Table.Body>
                {filteredAttributeOptions.map((attributeOption: AttributeOption, index: number) => {
                  const deleteOption = () => {
                    setShowDeleteConfirmationModal(false);
                    deleteAttributeOption(attributeOption.id);
                  };

                  return (
                    <TableRow
                      role="attribute-option-item"
                      isDraggable={isDraggable}
                      isSelected={selectedOptionId === attributeOption.id}
                      onClick={() => onSelectItem(attributeOption.id)}
                      key={`${attributeOption.code}${index}`}
                      data-testid={selectedOptionId === attributeOption.id ? 'is-selected' : 'is-not-selected'}
                    >
                      {!isDraggable && (
                        <TableCellNoDraggable>
                          <HandleContainer>
                            <RowIcon size={16} />
                          </HandleContainer>
                        </TableCellNoDraggable>
                      )}
                      <TableCellLabel role="attribute-option-item-label" rowTitle={true}>
                        {attributeOption.optionValues[locale] && attributeOption.optionValues[locale].value
                          ? attributeOption.optionValues[locale].value
                          : `[${attributeOption.code}]`}
                      </TableCellLabel>
                      <Table.Cell role="attribute-option-item-code">{attributeOption.code}</Table.Cell>
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
                          role="attribute-option-delete-button"
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
          </>
        )}
      </div>
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

const TableRow = styled(Table.Row)<{isDraggable: boolean} & AkeneoThemedProps>`
  td:first-child {
    color: ${({isDraggable}) => (isDraggable ? getColor('grey', 100) : getColor('grey', 40))};
  }
`;

const TableActionCell = styled(Table.ActionCell)`
  width: 50px;
`;

export default AttributeOptionTable;
