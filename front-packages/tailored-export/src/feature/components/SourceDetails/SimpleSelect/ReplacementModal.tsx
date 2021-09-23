import React, {useMemo, useState} from 'react';
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
  getLabel,
  NoDataSection,
  NoDataText,
  NoDataTitle,
  useTranslate,
  useUserContext,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {OPTION_COLLECTION_PAGE_SIZE, useAttributeOptions} from '../../../hooks/useAttributeOptions';
import {Attribute} from '../../../models';
import {filterEmptyValues, ReplacementValues} from '../common';
import {OnlyEmptyDropdown} from './OnlyEmptyDropdown';

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

type ReplacementModalProps = {
  initialMapping: ReplacementValues;
  attribute: Attribute;
  validationErrors: ValidationError[];
  onConfirm: (updatedReplacementValues: ReplacementValues) => void;
  onCancel: () => void;
};

const ReplacementModal = ({
  initialMapping,
  attribute,
  onConfirm,
  onCancel,
  validationErrors,
}: ReplacementModalProps) => {
  const translate = useTranslate();
  const [mapping, setMapping] = useState<{[from: string]: string}>(initialMapping);
  const [currentPage, setCurrentPage] = useState<number>(1);
  const [searchValue, setSearchValue] = useState<string>('');
  const debouncedSearchValue = useDebounce(searchValue);
  const catalogLocale = useUserContext().get('catalogLocale');
  const [onlyEmpty, setOnlyEmpty] = useState<boolean>(false);
  const mappingValidationErrors = filterErrors(validationErrors, '[mapping]');
  const optionCodesToExclude = useMemo(
    () => (onlyEmpty ? Object.keys(initialMapping) : []),
    [onlyEmpty, initialMapping]
  );

  const [attributeOptions, totalItems] = useAttributeOptions(
    attribute.code,
    debouncedSearchValue,
    currentPage,
    optionCodesToExclude
  );

  const updateMappedValue = (from: string, updatedValue: string) => {
    const updatedMapping = {...mapping, [from]: updatedValue};

    setMapping(updatedMapping);
  };

  const handleSearchChange = (updatedSearchValue: string) => {
    setSearchValue(updatedSearchValue);
    setCurrentPage(1);
  };

  const handleConfirm = () => onConfirm(filterEmptyValues(mapping));

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
              onSearchChange={handleSearchChange}
              placeholder={translate('pim_common.search')}
              searchValue={searchValue}
              title={translate('pim_common.search')}
            >
              <Search.ResultCount>
                {translate('pim_common.result_count', {itemsCount: totalItems}, totalItems)}
              </Search.ResultCount>
              <Search.Separator />
              <OnlyEmptyDropdown value={onlyEmpty} onChange={setOnlyEmpty} />
            </Search>
            {0 === attributeOptions.length ? (
              <NoDataSection>
                <AttributesIllustration size={256} />
                <NoDataTitle>
                  {translate(
                    'akeneo.tailored_export.column_details.sources.operation.replacement.modal.empty_result.title'
                  )}
                </NoDataTitle>
                <NoDataText>
                  {translate(
                    'akeneo.tailored_export.column_details.sources.operation.replacement.modal.empty_result.title'
                  )}
                </NoDataText>
              </NoDataSection>
            ) : (
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
                  {attributeOptions.map(attributeOption => (
                    <Table.Row key={attributeOption.code}>
                      <OptionLabelCell>
                        <OptionLabel title={getLabel(attributeOption.labels, catalogLocale, attributeOption.code)}>
                          {getLabel(attributeOption.labels, catalogLocale, attributeOption.code)}
                        </OptionLabel>
                      </OptionLabelCell>
                      <Table.Cell>
                        <Field>
                          <TextInput
                            placeholder={translate(
                              'akeneo.tailored_export.column_details.sources.operation.replacement.modal.table.field.to_placeholder'
                            )}
                            value={mapping[attributeOption.code] ?? ''}
                            onChange={newValue => updateMappedValue(attributeOption.code, newValue)}
                          />
                          {filterErrors(mappingValidationErrors, `[${attributeOption.code}]`).map((error, index) => (
                            <Helper key={index} inline={true} level="error">
                              {translate(error.messageTemplate, error.parameters)}
                            </Helper>
                          ))}
                        </Field>
                      </Table.Cell>
                    </Table.Row>
                  ))}
                </Table.Body>
              </Table>
            )}
          </TableContainer>
        </Content>
        {0 !== totalItems && (
          <Pagination
            currentPage={currentPage}
            itemsPerPage={OPTION_COLLECTION_PAGE_SIZE}
            totalItems={totalItems}
            followPage={setCurrentPage}
          />
        )}
      </Container>
    </Modal>
  );
};

export {ReplacementModal};
