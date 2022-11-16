import React, {useEffect, useState} from 'react';
import styled from 'styled-components';
import {
  AttributesIllustration,
  Button,
  Helper,
  Modal,
  Pagination,
  Placeholder,
  Search,
  Table,
  TagInput,
  useDebounce,
} from 'akeneo-design-system';
import {
  filterErrors,
  formatParameters,
  getLabel,
  LabelCollection,
  NotificationLevel,
  useNotify,
  useRoute,
  useTranslate,
  useUserContext,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {MappedFilterDropdown, MappedFilterValue} from './MappedFilterDropdown';
import {isDefaultReplacementValueFilter, ReplacementValueFilter} from './ReplacementValueFilter';
import {filterEmptyValues, OperationType, ReplacementValues} from '../../../../models';

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

const ValueLabelCell = styled(Table.Cell)`
  max-width: unset;
  width: 0;
`;

const ValueLabel = styled.div`
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

type Value = {
  code: string;
  labels: LabelCollection;
};

type ReplacementModalProps = {
  title: string;
  replacedValuesHeader: string;
  operationType: OperationType;
  operationUuid: string;
  initialMapping: ReplacementValues;
  values: Value[];
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
  title,
  replacedValuesHeader,
  operationType,
  operationUuid,
  initialMapping,
  values,
  totalItems,
  itemsPerPage,
  replacementValueFilter,
  onConfirm,
  onReplacementValueFilterChange,
  onCancel,
}: ReplacementModalProps) => {
  const translate = useTranslate();
  const [mapping, setMapping] = useState<ReplacementValues>(initialMapping);
  const validateReplacementOperationRoute = useRoute('pimee_tailored_import_validate_replacement_operation_action');
  const notify = useNotify();
  const catalogLocale = useUserContext().get('catalogLocale');
  const [replacementOperationValidationErrors, setReplacementOperationValidationErrors] = useState<ValidationError[]>(
    []
  );
  const mappingValidationErrors = filterErrors(replacementOperationValidationErrors, '[mapping]');
  const [searchValue, setSearchValue] = useState<string>(replacementValueFilter.searchValue);
  const debouncedSearchValue = useDebounce(searchValue);
  const [mappedFilterValue, setMappedFilterValue] = useState<MappedFilterValue>('all');

  const updateMappedValue = (from: string, updatedValue: string[]) =>
    setMapping(mapping => filterEmptyValues({...mapping, [from]: updatedValue}));

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
    const [codesToInclude, codesToExclude] = getIncludeExcludeCodes(mappedFilterValue, mapping);

    setMappedFilterValue(mappedFilterValue);
    onReplacementValueFilterChange(replacementValueFilter => ({
      ...replacementValueFilter,
      codesToInclude,
      codesToExclude,
      page: 1,
    }));
  };

  const handleConfirm = async () => {
    const response = await fetch(validateReplacementOperationRoute, {
      body: JSON.stringify({
        uuid: operationUuid,
        type: operationType,
        mapping,
      }),
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      method: 'POST',
    });

    setReplacementOperationValidationErrors([]);

    if (response.ok) {
      onConfirm(mapping);
    } else {
      try {
        const errors = await response.json();

        setReplacementOperationValidationErrors(formatParameters(errors));
      } catch (error) {}

      notify(
        NotificationLevel.ERROR,
        translate('akeneo.tailored_import.data_mapping.operations.replacement.modal.validation_error')
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
          {translate('akeneo.tailored_import.data_mapping.operations.replacement.modal.subtitle')}
        </Modal.SectionTitle>
        <Modal.Title>{title}</Modal.Title>
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
              <Placeholder
                size="large"
                title={translate('akeneo.tailored_import.data_mapping.operations.replacement.modal.no_result.title')}
                illustration={<AttributesIllustration />}
              />
            )}
            {!isDefaultReplacementValueFilter(replacementValueFilter) && 0 === values.length && (
              <Placeholder
                size="large"
                title={translate('akeneo.tailored_import.data_mapping.operations.replacement.modal.empty_result.title')}
                illustration={<AttributesIllustration />}
              >
                {translate('akeneo.tailored_import.data_mapping.operations.replacement.modal.empty_result.text')}
              </Placeholder>
            )}
            {0 < values.length && (
              <Table>
                <Table.Header sticky={44}>
                  <Table.HeaderCell>{replacedValuesHeader}</Table.HeaderCell>
                  <Table.HeaderCell>
                    {translate(
                      'akeneo.tailored_import.data_mapping.operations.replacement.modal.table.header.source_values'
                    )}
                  </Table.HeaderCell>
                </Table.Header>
                <Table.Body>
                  {values.map(value => {
                    const valueErrors = filterErrors(mappingValidationErrors, `[${value.code}]`);

                    return (
                      <Table.Row key={value.code}>
                        <ValueLabelCell>
                          <ValueLabel title={getLabel(value.labels, catalogLocale, value.code)}>
                            {getLabel(value.labels, catalogLocale, value.code)}
                          </ValueLabel>
                        </ValueLabelCell>
                        <Table.Cell>
                          <Field>
                            <TagInput
                              invalid={0 < valueErrors.length}
                              separators={[]}
                              placeholder={translate(
                                'akeneo.tailored_import.data_mapping.operations.replacement.to_placeholder'
                              )}
                              value={mapping[value.code] ?? []}
                              onChange={newValue => updateMappedValue(value.code, newValue)}
                            />
                            {valueErrors.map((error, index) => (
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
