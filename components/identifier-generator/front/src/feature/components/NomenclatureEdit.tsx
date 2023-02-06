import React, {FC, useCallback, useEffect, useState} from 'react';
import {
  Button,
  Checkbox,
  Modal,
  NumberInput,
  Pagination,
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
    total,
  } = useGetFamilyNomenclatureValues(nomenclature, filter, valuesToSave);
  const {save} = useSaveNomenclature();

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
    if (nomenclature) {
      save(
        {...nomenclature, propertyCode: 'family', values: valuesToSave},
        {
          onError: (violations: Violation[]) => {
            setViolations(violations);
            notify(NotificationLevel.ERROR, translate('TODO'));
          },
        }
      );
    }
  };

  return (
    <>
      <button onClick={open}>Open nomenclature</button> {/* TODO */}
      {isOpen && (
        <Modal closeTitle="TODO Close" onClose={close}>
          <Modal.TopRightButtons>
            <Button onClick={handleSaveNomenclature}>Save TODO</Button>
          </Modal.TopRightButtons>
          <Modal.SectionTitle color="brand">Generating my identifiers for Families TODO</Modal.SectionTitle>
          <Modal.Title>Manage nomenclature TODO</Modal.Title>
          {nomenclature && (
            <>
              <SectionTitle>
                <SectionTitle.Title>Families TODO</SectionTitle.Title>
                <NomenclatureValuesDisplayFilter filter={filter} onChange={onFilterChange} />
              </SectionTitle>
              <Table>
                <Table.Body>
                  <Table.Row>
                    <Styled.TitleCell>Characters number (required)</Styled.TitleCell>
                    <Table.Cell>
                      <OperatorSelector
                        operators={[Operator.EQUALS, Operator.LOWER_OR_EQUAL_THAN]}
                        operator={nomenclature.operator}
                        onChange={handleChangeOperator}
                        invalid={!!violations.find(violation => violation.path === 'operator')}
                        placeholder={'TODO'}
                      />
                      <NumberInput
                        value={`${nomenclature.value || ''}`}
                        onChange={handleValueChange}
                        invalid={!!violations.find(violation => violation.path === 'value')}
                        placeholder={'todo placeholder'}
                      />
                      <Checkbox checked={nomenclature.generate_if_empty} onChange={handleGenerateIfEmptyChange} />
                      Generate nomenclature automatically
                    </Table.Cell>
                  </Table.Row>
                </Table.Body>
              </Table>
              <div style={{flexBasis: 'calc(100vh - 300px)', overflow: 'auto', width: 'calc(100vw - 160px)'}}>
                <Search searchValue={search} onSearchChange={handleSearchChange} />
                <Pagination currentPage={page} itemsPerPage={25} totalItems={total} followPage={setPage} />
                <Table>
                  <Table.Header>
                    <Table.HeaderCell>Label TODO</Table.HeaderCell>
                    <Table.HeaderCell>Code TODO</Table.HeaderCell>
                    <Table.HeaderCell>Nomenclature TODO</Table.HeaderCell>
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
                  </Table.Body>
                </Table>
                <Pagination currentPage={page} itemsPerPage={25} totalItems={total} followPage={setPage} />
              </div>
            </>
          )}
        </Modal>
      )}
    </>
  );
};

export {NomenclatureEdit};
