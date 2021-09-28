import React, {useEffect, useState} from 'react';
import styled from 'styled-components';
import {
  AttributesIllustration,
  Button,
  Helper,
  Modal,
  Pagination,
  Search,
  Table,
  TextInput,
  useDebounce,
} from 'akeneo-design-system';
import {
  filterErrors,
  formatParameters,
  getLabel,
  LabelCollection,
  NoDataSection,
  NoDataText,
  NoDataTitle,
  NotificationLevel,
  useNotify,
  useRoute,
  useTranslate,
  useUserContext,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {filterEmptyValues, ReplacementValues} from '../../common';
import {MappedFilterDropdown, MappedFilterValue} from './MappedFilterDropdown';
import {isDefaultReplacementValueFilter, ReplacementValueFilter} from './ReplacementValueFilter';

const Container = styled.div`
  width: 100%;
  max-height: 100vh;
  padding-top: 40px;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
`;

const Content = styled.div`
  display: flex;
  flex-direction: column;
  width: 100%;
  flex: 1;
  overflow: auto;
  overflow-x: hidden;
`;

const TableContainer = styled.div`
  flex: 1;
`;

const OptionLabelCell = styled(Table.Cell)`
  max-width: unset;
  width: 0;
`;

const OptionLabel = styled.div`
  width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
`;

const Field = styled.div`
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 5px;
`;

type Value = {
  code: string;
  labels: LabelCollection;
};

const getIncludeExcludeCodes = (
  mappedFilterValue: MappedFilterValue,
  mapping: ReplacementValues
): (string[] | null)[] => {
  switch (mappedFilterValue) {
    case 'all':
      return [null, null];
    case 'mapped':
      return [Object.keys(mapping), null];
    case 'unmapped':
      return [null, Object.keys(mapping)];
  }
};

type ReplacementModalProps = {
  initialMapping: ReplacementValues;
  values: Value[];
  validationErrors: ValidationError[];
  totalItems: number;
  itemsPerPage: number;
  replacementValueFilter: ReplacementValueFilter;
  onConfirm: (updatedReplacementValues: ReplacementValues) => void;
  onCancel: () => void;
  onReplacementValueFilterChange: (
    setFilter: (previousFilter: ReplacementValueFilter) => ReplacementValueFilter
  ) => void;
};

const ReplacementModal = ({
  initialMapping,
  values,
  totalItems,
  itemsPerPage,
  replacementValueFilter,
  validationErrors,
  onConfirm,
  onReplacementValueFilterChange,
  onCancel,
}: ReplacementModalProps) => {
  const translate = useTranslate();
  const [mapping, setMapping] = useState<ReplacementValues>(initialMapping);
  const validateReplacementOperationRoute = useRoute('pimee_tailored_export_validate_replacement_operation_action');
  const notify = useNotify();
  const catalogLocale = useUserContext().get('catalogLocale');
  const [replacementOperationValidationErrors, setReplacementOperationValidationErrors] = useState(validationErrors);
  const mappingValidationErrors = filterErrors(replacementOperationValidationErrors, '[mapping]');
  const [searchValue, setSearchValue] = useState<string>(replacementValueFilter.searchValue);
  const debouncedSearchValue = useDebounce(searchValue);
  const [mappedFilterValue, setMappedFilterValue] = useState<MappedFilterValue>('all');

  const updateMappedValue = (from: string, updatedValue: string) => {
    const updatedMapping = {...mapping, [from]: updatedValue};

    setMapping(updatedMapping);
  };

  useEffect(() => {
    onReplacementValueFilterChange(replacementValueFilter => ({
      ...replacementValueFilter,
      page: 1,
      searchValue: debouncedSearchValue,
    }));
  }, [debouncedSearchValue, onReplacementValueFilterChange]);

  const handlePageChange = (page: number) => {
    onReplacementValueFilterChange(replacementValueFilter => ({
      ...replacementValueFilter,
      page,
    }));
  };

  const handleMappedFilterValueChange = (mappedFilterValue: MappedFilterValue) => {
    const [codesToInclude, codesToExclude] = getIncludeExcludeCodes(mappedFilterValue, initialMapping);

    setMappedFilterValue(mappedFilterValue);
    onReplacementValueFilterChange(replacementValueFilter => ({
      ...replacementValueFilter,
      codesToInclude,
      codesToExclude,
      page: 1,
    }));
  };

  const handleConfirm = async () => {
    const values = filterEmptyValues(mapping);

    const response = await fetch(validateReplacementOperationRoute, {
      body: JSON.stringify({
        type: 'replacement',
        mapping: values,
      }),
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      method: 'POST',
    });

    setReplacementOperationValidationErrors([]);

    if (response.ok) {
      onConfirm(values);
    } else {
      try {
        const errors = await response.json();

        setReplacementOperationValidationErrors(formatParameters(errors));
      } catch (error) {}

      notify(
        NotificationLevel.ERROR,
        translate('akeneo.tailored_export.column_details.sources.operation.replacement.modal.validation_error')
      );
    }
  };

  return (
    <Modal onClose={onCancel} closeTitle={translate('pim_common.close')}>
      <Modal.TopRightButtons>
        <Button level="primary" onClick={handleConfirm}>
          {translate('pim_common.confirm')}
        </Button>
      </Modal.TopRightButtons>
      <Container>
        <Modal.SectionTitle color="brand">
          {translate('akeneo.tailored_export.column_details.sources.operation.replacement.modal.title')}
        </Modal.SectionTitle>
        <Modal.Title>
          {translate('akeneo.tailored_export.column_details.sources.operation.replacement.modal.subtitle')}
        </Modal.Title>
        <Content>
          <TableContainer>
            <Search
              sticky={0}
              onSearchChange={setSearchValue}
              placeholder={translate('pim_common.search')}
              searchValue={searchValue}
              title={translate('pim_common.search')}
            >
              <Search.ResultCount>
                {translate('pim_common.result_count', {itemsCount: totalItems}, totalItems)}
              </Search.ResultCount>
              <Search.Separator />
              <MappedFilterDropdown value={mappedFilterValue} onChange={handleMappedFilterValueChange} />
            </Search>
            {isDefaultReplacementValueFilter(replacementValueFilter) && 0 === values.length && (
              <NoDataSection>
                <AttributesIllustration size={256} />
                <NoDataTitle>
                  {translate(
                    'akeneo.tailored_export.column_details.sources.operation.replacement.modal.no_result.title'
                  )}
                </NoDataTitle>
              </NoDataSection>
            )}
            {!isDefaultReplacementValueFilter(replacementValueFilter) && 0 === values.length && (
              <NoDataSection>
                <AttributesIllustration size={256} />
                <NoDataTitle>
                  {translate(
                    'akeneo.tailored_export.column_details.sources.operation.replacement.modal.empty_result.title'
                  )}
                </NoDataTitle>
                <NoDataText>
                  {translate(
                    'akeneo.tailored_export.column_details.sources.operation.replacement.modal.empty_result.text'
                  )}
                </NoDataText>
              </NoDataSection>
            )}
            {0 < values.length && (
              <Table>
                <Table.Header sticky={44}>
                  <Table.HeaderCell>
                    {translate(
                      'akeneo.tailored_export.column_details.sources.operation.replacement.modal.table.header.values'
                    )}
                  </Table.HeaderCell>
                  <Table.HeaderCell>
                    {translate(
                      'akeneo.tailored_export.column_details.sources.operation.replacement.modal.table.header.replacement'
                    )}
                  </Table.HeaderCell>
                </Table.Header>
                <Table.Body>
                  {values.map(attributeOption => {
                    const optionErrors = filterErrors(mappingValidationErrors, `[${attributeOption.code}]`);

                    return (
                      <Table.Row key={attributeOption.code}>
                        <OptionLabelCell>
                          <OptionLabel title={getLabel(attributeOption.labels, catalogLocale, attributeOption.code)}>
                            {getLabel(attributeOption.labels, catalogLocale, attributeOption.code)}
                          </OptionLabel>
                        </OptionLabelCell>
                        <Table.Cell>
                          <Field>
                            <TextInput
                              invalid={0 < optionErrors.length}
                              placeholder={translate(
                                'akeneo.tailored_export.column_details.sources.operation.replacement.modal.table.field.to_placeholder'
                              )}
                              value={mapping[attributeOption.code] ?? ''}
                              onChange={newValue => updateMappedValue(attributeOption.code, newValue)}
                            />
                            {optionErrors.map((error, index) => (
                              <Helper key={index} inline={true} level="error">
                                {translate(error.messageTemplate, error.parameters)}
                              </Helper>
                            ))}
                          </Field>
                        </Table.Cell>
                      </Table.Row>
                    );
                  })}
                </Table.Body>
              </Table>
            )}
          </TableContainer>
        </Content>
        {0 !== totalItems && (
          <Pagination
            currentPage={replacementValueFilter.page}
            itemsPerPage={itemsPerPage}
            totalItems={totalItems}
            followPage={handlePageChange}
          />
        )}
      </Container>
    </Modal>
  );
};

export {ReplacementModal};
