import React, {useState} from 'react';
import styled from 'styled-components';
import {AttributesIllustration, Button, Helper, Modal, Placeholder, Search, Table} from 'akeneo-design-system';
import {
  filterErrors,
  formatParameters,
  NotificationLevel,
  useNotify,
  useRoute,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {SearchAndReplaceValue, SEARCH_AND_REPLACE_OPERATION_TYPE} from './SearchAndReplaceOperationBlock';
import {
  filterEmptyReplacements,
  filterOnSearchValue,
  getDefaultSearchAndReplaceValue,
  updateByIndex,
  updateByUuid,
} from '../../../../../models';
import {SearchAndReplaceValueRow} from './SearchAndReplaceValueRow';

const MAX_REPLACEMENTS = 10;

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

type SearchAndReplaceModalProps = {
  operationUuid: string;
  initialReplacements: SearchAndReplaceValue[];
  onConfirm: (updatedReplacements: SearchAndReplaceValue[]) => void;
  onCancel: () => void;
};

const SearchAndReplaceModal = ({
  operationUuid,
  initialReplacements,
  onConfirm,
  onCancel,
}: SearchAndReplaceModalProps) => {
  const translate = useTranslate();
  const [replacements, setReplacements] = useState<(SearchAndReplaceValue | undefined)[]>(initialReplacements);
  const validationRoute = useRoute('pimee_tailored_import_validate_search_and_replace_operation_action');
  const notify = useNotify();
  const [validationErrors, setValidationErrors] = useState<ValidationError[]>([]);
  const [searchValue, setSearchValue] = useState<string>('');

  const filteredReplacements = filterOnSearchValue(replacements, searchValue);

  const handleConfirm = async () => {
    const filteredReplacements = filterEmptyReplacements(replacements);

    const response = await fetch(validationRoute, {
      body: JSON.stringify({
        uuid: operationUuid,
        type: SEARCH_AND_REPLACE_OPERATION_TYPE,
        replacements: filteredReplacements,
      }),
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      method: 'POST',
    });

    setValidationErrors([]);

    if (response.ok) {
      onConfirm(filteredReplacements);
    } else {
      try {
        const errors = await response.json();

        setValidationErrors(filterErrors(formatParameters(errors), '[replacements]'));
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
          {translate('akeneo.tailored_import.data_mapping.operations.title')}
        </Modal.SectionTitle>
        <Modal.Title>
          {translate('akeneo.tailored_import.data_mapping.operations.search_and_replace.title')}
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
                {translate(
                  'pim_common.result_count',
                  {itemsCount: '' === searchValue ? MAX_REPLACEMENTS : filteredReplacements.length},
                  '' === searchValue ? MAX_REPLACEMENTS : filteredReplacements.length
                )}
              </Search.ResultCount>
            </Search>
            {'' !== searchValue && 0 === filteredReplacements.length ? (
              <Placeholder
                size="large"
                title={translate('akeneo.tailored_import.data_mapping.operations.replacement.modal.empty_result.title')}
                illustration={<AttributesIllustration />}
              >
                {translate('akeneo.tailored_import.data_mapping.operations.replacement.modal.empty_result.text')}
              </Placeholder>
            ) : (
              <>
                <Helper>{translate('akeneo.tailored_import.data_mapping.operations.search_and_replace.helper')}</Helper>
                <Table>
                  <Table.Header sticky={44}>
                    <Table.HeaderCell>
                      {translate('akeneo.tailored_import.data_mapping.operations.search_and_replace.what.header')}
                    </Table.HeaderCell>
                    <Table.HeaderCell>
                      {translate('akeneo.tailored_import.data_mapping.operations.search_and_replace.with.header')}
                    </Table.HeaderCell>
                    <Table.HeaderCell>
                      {translate('akeneo.tailored_import.data_mapping.operations.search_and_replace.case_sensitive')}
                    </Table.HeaderCell>
                  </Table.Header>
                  <Table.Body>
                    {'' === searchValue
                      ? [...Array(MAX_REPLACEMENTS)].map((_, index) => {
                          const replacement = replacements[index] ?? getDefaultSearchAndReplaceValue();

                          return (
                            <SearchAndReplaceValueRow
                              key={index}
                              validationErrors={filterErrors(validationErrors, `[${replacement.uuid}]`)}
                              replacement={replacements[index] ?? getDefaultSearchAndReplaceValue()}
                              onReplacementChange={replacement =>
                                setReplacements(replacements => updateByIndex(replacements, replacement, index))
                              }
                            />
                          );
                        })
                      : filteredReplacements.map((replacement, index) => (
                          <SearchAndReplaceValueRow
                            key={replacement.uuid}
                            validationErrors={filterErrors(validationErrors, `[${replacement.uuid}]`)}
                            replacement={replacement}
                            onReplacementChange={replacement =>
                              setReplacements(replacements => updateByUuid(replacements, replacement))
                            }
                          />
                        ))}
                  </Table.Body>
                </Table>
              </>
            )}
          </TableContainer>
        </Content>
      </Container>
    </Modal>
  );
};

export {SearchAndReplaceModal};
