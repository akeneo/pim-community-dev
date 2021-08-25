import React, {useCallback, useContext, useEffect, useState} from 'react';
import {SearchBar, useDebounceCallback, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {AttributeOption} from '../model';
import {AttributeOptionsContext, useAttributeContext} from '../contexts';
import {useSortedAttributeOptions} from '../hooks';
import AutoOptionSorting from './AutoOptionSorting';
import NewOptionPlaceholder from './NewOptionPlaceholder';
import {
  AkeneoThemedProps,
  Button,
  CloseIcon,
  getColor,
  IconButton,
  RowIcon,
  SectionTitle,
  Table,
} from 'akeneo-design-system';
import styled from 'styled-components';
import DeleteConfirmationModal from './DeleteConfirmationModal';
import NoResultOnSearch from './NoResultOnSearch';
import AttributeOptionQualityBadge from './AttributeOptionQualityBadge';
import {AttributeOptionRow} from './AttributeOptionRow';

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
  const {attributeOptions} = useContext(AttributeOptionsContext);
  const {sortedAttributeOptions, setSortedAttributeOptions} = useSortedAttributeOptions(
    attributeOptions,
    attributeContext.autoSortOptions
  );
  const [filteredAttributeOptions, setFilteredAttributeOptions] =
    useState<AttributeOption[] | null>(sortedAttributeOptions);
  const [showNewOptionPlaceholder, setShowNewOptionPlaceholder] = useState<boolean>(isNewOptionFormDisplayed);
  const [isDraggable, setIsDraggable] = useState<boolean>(attributeContext.autoSortOptions);
  const [searchValue, setSearchValue] = useState('');
  const [autoSortingReadOnly, setAutoSortingReadOnly] = useState<boolean>(false);
  const [attributeOptionToDelete, setAttributeOptionToDelete] = useState<AttributeOption | null>(null);

  const onSelectItem = useCallback(
    (optionId: number) => {
      setShowNewOptionPlaceholder(false);
      selectAttributeOption(optionId);
      showNewOptionForm(false);
    },
    [selectAttributeOption, showNewOptionForm]
  );

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

  const reorderAttributeOptions = useCallback(
    (newIndices: number[]) => {
      setSortedAttributeOptions((rows: AttributeOption[]): AttributeOption[] => {
        const newSortedAttributeOptions = newIndices.map(index => rows[index]).filter(index => index !== undefined);

        manuallySortAttributeOptions(newSortedAttributeOptions);

        return newSortedAttributeOptions;
      });
    },
    [manuallySortAttributeOptions]
  );

  const filterOnLabelOrCode = useCallback(
    (searchString: string) => {
      if (sortedAttributeOptions) {
        setFilteredAttributeOptions(
          sortedAttributeOptions.filter((attributeOption: AttributeOption) => {
            return (
              attributeOption.code.toLocaleLowerCase().includes(searchString.toLowerCase().trim()) ||
              attributeOption.optionValues[locale].value.toLocaleLowerCase().includes(searchString.toLowerCase().trim())
            );
          })
        );
      }
    },
    [sortedAttributeOptions]
  );

  const debouncedSearch = useDebounceCallback(filterOnLabelOrCode, 300);

  const onSearch = (searchValue: string) => {
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
    debouncedSearch(searchValue);
  };

  const attributeOptionsCount = null === attributeOptions ? 0 : attributeOptions.length;

  const filteredAttributeOptionsCount = null === filteredAttributeOptions ? 0 : filteredAttributeOptions.length;

  useEffect(() => {
    if (selectedOptionId !== null) {
      setShowNewOptionPlaceholder(false);
    }
  }, [selectedOptionId]);

  useEffect(() => {
    attributeContext.autoSortOptions ? setIsDraggable(false) : setIsDraggable(true);
  }, [attributeContext.autoSortOptions]);

  useEffect(() => {
    setFilteredAttributeOptions(sortedAttributeOptions);
    setSearchValue('');
  }, [sortedAttributeOptions]);

  const handleReorder = useCallback(newIndices => reorderAttributeOptions(newIndices), [reorderAttributeOptions]);

  return (
    <div className="AknSubsection AknAttributeOption-list">
      <SectionTitleStyled>
        <SectionTitle.Title>
          {translate('pim_enrich.entity.attribute_option.module.edit.options_codes')}
        </SectionTitle.Title>
        <SectionTitle.Spacer />
        <Button
          size={'small'}
          ghost
          level="tertiary"
          onClick={() => displayNewOptionPlaceholder()}
          data-testid="add-new-attribute-option-button"
        >
          {translate('pim_enrich.entity.product.module.attribute.add_option')}
        </Button>
      </SectionTitleStyled>

      <SearchBar
        placeholder={translate('pim_enrich.entity.attribute_option.module.edit.search.placeholder')}
        count={filteredAttributeOptionsCount}
        searchValue={searchValue}
        onSearchChange={onSearch}
      />

      <div data-testid="attribute-options-list" data-attribute-option-role="list">
        {filteredAttributeOptionsCount === 0 && attributeOptionsCount > 0 && <NoResultOnSearch />}

        {filteredAttributeOptionsCount > 0 && filteredAttributeOptions !== null && (
          <>
            <AutoOptionSorting readOnly={autoSortingReadOnly} />

            <SpacedTable isDragAndDroppable={isDraggable} onReorder={handleReorder}>
              <Table.Header sticky={44}>
                <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
                <Table.HeaderCell>{translate('pim_common.code')}</Table.HeaderCell>
                <Table.HeaderCell>&nbsp;</Table.HeaderCell>
                <Table.HeaderCell>&nbsp;</Table.HeaderCell>
              </Table.Header>
              <Table.Body>
                {filteredAttributeOptions.map((attributeOption: AttributeOption) => {
                  return (
                    <AttributeOptionRow
                      attributeOption={attributeOption}
                      onSelectItem={onSelectItem}
                      isSelected={selectedOptionId === attributeOption.id}
                      onDelete={setAttributeOptionToDelete}
                      key={`${attributeContext.attributeId}-${attributeOption.code}`}
                    />
                  );
                })}

                {showNewOptionPlaceholder && (
                  <NewOptionPlaceholder cancelNewOption={cancelNewOption} isDraggable={isDraggable} />
                )}
              </Table.Body>
            </SpacedTable>

            {attributeOptionToDelete && (
              <DeleteConfirmationModal
                attributeOptionCode={attributeOptionToDelete.code}
                confirmDelete={() => {
                  deleteAttributeOption(attributeOptionToDelete.id);
                  setAttributeOptionToDelete(null);
                }}
                cancelDelete={() => setAttributeOptionToDelete(null)}
              />
            )}
          </>
        )}
      </div>
    </div>
  );
};

const SectionTitleStyled = styled(SectionTitle)`
  margin: 20px 0 20px 0;
`;

const SpacedTable = styled(Table)`
  th {
    padding-top: 15px;
  }
`;

export default AttributeOptionTable;
