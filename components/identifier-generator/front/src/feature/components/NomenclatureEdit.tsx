import React, {FC, useCallback, useEffect, useState} from 'react';
import {
  AttributesIllustration,
  Button,
  Checkbox,
  Modal,
  NumberInput,
  Pagination,
  Placeholder,
  Search,
  SectionTitle,
  Table,
  useBooleanState,
} from 'akeneo-design-system';
import {Styled} from './Styled';
import {useGetFamilyNomenclatureValues, useGetNomenclature, useSaveNomenclature} from '../hooks';
import {OperatorSelector} from './OperatorSelector';
import {Nomenclature, NomenclatureFilter, NomenclatureValues, Operator} from '../models';
import {NomenclatureLineEdit} from './NomenclatureLineEdit';
import {NomenclatureValuesDisplayFilter} from './NomenclatureValuesDisplayFilter';
import {Violation} from '../validators';
import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/shared';

type NomenclatureEditProps = {};

const NomenclatureEdit: FC<NomenclatureEditProps> = () => {
  const translate = useTranslate();
  const notify = useNotify();
  const [isOpen, open, close] = useBooleanState();
  const [nomenclature, setNomenclature] = useState<Nomenclature | undefined>(undefined);
  const {data: fetchedNomenclature} = useGetNomenclature('family');
  const [filter, setFilter] = useState<NomenclatureFilter>('all');
  const [valuesToSave, setValuesToSave] = useState<NomenclatureValues>({});
  const [violations, setViolations] = useState<Violation[]>([]);
  const {
    data: nomenclatureLines,
    page,
    setPage,
    search,
    setSearch,
    totalValuesCount,
    filteredValuesCount,
    hasValueInvalid,
  } = useGetFamilyNomenclatureValues(nomenclature, filter, valuesToSave);
  const {save, isLoading: isSaving} = useSaveNomenclature();

  const onFilterChange = (value: NomenclatureFilter) => {
    if (nomenclature) setNomenclature({...nomenclature, values: valuesToSave});
    setFilter(value);
  };

  useEffect(() => {
    if (fetchedNomenclature) {
      setNomenclature(fetchedNomenclature);
      setValuesToSave(fetchedNomenclature.values);
    }
  }, [fetchedNomenclature]);

  const handleValueToSaveChange = useCallback(
    (familyCode, value) => {
      setValuesToSave({...valuesToSave, [familyCode]: value});
    },
    [valuesToSave]
  );

  const handleChangeOperator = useCallback(
    operator => {
      if (nomenclature) {
        setNomenclature({...nomenclature, operator: operator});
      }
    },
    [nomenclature]
  );

  const handleValueChange = useCallback(
    (value: string) => {
      if (nomenclature) {
        setNomenclature({...nomenclature, value: value === '' ? null : Number.parseInt(value)});
      }
    },
    [nomenclature]
  );

  const handleGenerateIfEmptyChange = useCallback(
    generate_if_empty => {
      if (nomenclature) {
        setNomenclature({...nomenclature, generate_if_empty});
      }
    },
    [nomenclature]
  );

  const handleSearchChange = useCallback(
    search => {
      setSearch(search);
    },
    [setSearch]
  );

  const handleSaveNomenclature = () => {
    if (nomenclature && !isSaving) {
      save(
        {...nomenclature, propertyCode: 'family', values: valuesToSave},
        {
          onError: (violations: Violation[]) => {
            setViolations(violations);
            notify(NotificationLevel.ERROR, translate('pim_identifier_generator.nomenclature.flash.error'));
          },
          onSuccess: () => {
            close();
            setViolations([]);
            if (hasValueInvalid) {
              notify(NotificationLevel.WARNING, translate('pim_identifier_generator.nomenclature.flash.warning'));
            } else {
              notify(NotificationLevel.SUCCESS, translate('pim_identifier_generator.nomenclature.flash.success'));
            }
          },
        }
      );
    }
  };

  return (
    <>
      <button onClick={open}>Open nomenclature</button> {/* TODO */}
      {isOpen && (
        <Modal closeTitle={translate('pim_common.close')} onClose={close}>
          <Modal.TopRightButtons>
            <Button onClick={handleSaveNomenclature} disabled={isSaving}>
              {translate('pim_common.save')}
            </Button>
          </Modal.TopRightButtons>
          <Modal.SectionTitle color="brand">
            {translate('pim_identifier_generator.nomenclature.section_title', {
              property: translate('pim_enrich.entity.family.plural_label'),
            })}
          </Modal.SectionTitle>
          <Modal.Title>{translate('pim_identifier_generator.nomenclature.title')}</Modal.Title>
          {nomenclature && (
            <Styled.NomenclatureModalContent>
              <SectionTitle>
                <SectionTitle.Title>
                  {translate('pim_enrich.entity.family.page_title.index', {count: totalValuesCount}, totalValuesCount)}
                </SectionTitle.Title>
                <SectionTitle.Spacer />
                <NomenclatureValuesDisplayFilter filter={filter} onChange={onFilterChange} />
              </SectionTitle>
              <Styled.NomenclatureDefinition>
                <Table.Body>
                  <Table.Row>
                    <Styled.TitleCell>
                      {translate('pim_identifier_generator.nomenclature.characters_number')}
                      &nbsp;<em>{translate('pim_common.required_label')}</em>
                    </Styled.TitleCell>
                    <Table.Cell>
                      <OperatorSelector
                        operators={[Operator.EQUALS, Operator.LOWER_OR_EQUAL_THAN]}
                        operator={nomenclature.operator}
                        onChange={handleChangeOperator}
                        invalid={!!violations.find(violation => violation.path === 'operator')}
                        placeholder={translate('pim_identifier_generator.nomenclature.operator_placeholder')}
                        isInSelection={false}
                      />
                    </Table.Cell>
                    <Table.Cell>
                      <NumberInput
                        value={`${nomenclature.value || ''}`}
                        onChange={handleValueChange}
                        invalid={!!violations.find(violation => violation.path === 'value')}
                        placeholder={translate('pim_identifier_generator.nomenclature.value_placeholder')}
                        min={1}
                        max={5}
                      />
                    </Table.Cell>
                    <Table.Cell>
                      <Styled.CheckboxContainer>
                        <Checkbox checked={nomenclature.generate_if_empty} onChange={handleGenerateIfEmptyChange}>
                          {translate('pim_identifier_generator.nomenclature.generate_if_empty')}
                        </Checkbox>
                      </Styled.CheckboxContainer>
                    </Table.Cell>
                  </Table.Row>
                </Table.Body>
              </Styled.NomenclatureDefinition>
              <Search
                searchValue={search}
                onSearchChange={handleSearchChange}
                placeholder={translate('pim_common.search')}
              >
                <Search.ResultCount>
                  {translate('pim_common.result_count', {itemsCount: filteredValuesCount}, filteredValuesCount)}
                </Search.ResultCount>
              </Search>
              <Pagination currentPage={page} itemsPerPage={25} totalItems={filteredValuesCount} followPage={setPage} />
              <Styled.NomenclatureTable>
                <Table>
                  <Table.Header>
                    <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
                    <Table.HeaderCell>{translate('pim_common.code')}</Table.HeaderCell>
                    <Table.HeaderCell>
                      {translate('pim_identifier_generator.nomenclature.nomenclature')}{' '}
                    </Table.HeaderCell>
                  </Table.Header>
                  <Table.Body>
                    {nomenclatureLines?.map(nomenclatureLine => (
                      <NomenclatureLineEdit
                        nomenclature={nomenclature}
                        nomenclatureLine={nomenclatureLine}
                        onChange={handleValueToSaveChange}
                        key={nomenclatureLine.code}
                      />
                    ))}
                    {nomenclatureLines && nomenclatureLines.length === 0 && (
                      <tr>
                        <td colSpan={3}>
                          <Placeholder
                            size={'large'}
                            illustration={<AttributesIllustration />}
                            title={translate('pim_datagrid.no_entities', {
                              entityHint: translate('pim_enrich.entity.family.plural_label'),
                            })}
                          >
                            {translate('pim_datagrid.no_results_subtitle')}
                          </Placeholder>
                        </td>
                      </tr>
                    )}
                  </Table.Body>
                </Table>
              </Styled.NomenclatureTable>
              <Pagination currentPage={page} itemsPerPage={25} totalItems={filteredValuesCount} followPage={setPage} />
            </Styled.NomenclatureModalContent>
          )}
        </Modal>
      )}
    </>
  );
};

export {NomenclatureEdit};
