import React, {FC, useCallback, useEffect, useState} from 'react';
import {Checkbox, Modal, NumberInput, Search, SectionTitle, Table, useBooleanState} from 'akeneo-design-system';
import {Styled} from './Styled';
import {useGetFamilyNomenclatureValues, useGetNomenclature} from '../hooks';
import {OperatorSelector} from './OperatorSelector';
import {Nomenclature, NomenclatureFilter, NomenclatureValues, Operator} from '../models';
import {NomenclatureLineEdit} from './NomenclatureLineEdit';
import {NomenclatureValuesDisplayFilter} from './NomenclatureValuesDisplayFilter';

type NomenclatureEditProps = {};

const NomenclatureEdit: FC<NomenclatureEditProps> = () => {
  const [isOpen, open, close] = useBooleanState();
  const [nomenclature, setNomenclature] = useState<Nomenclature | undefined>(undefined);
  const {data: fetchedNomenclature} = useGetNomenclature('family');
  const [search, setSearch] = useState<string>('');
  const [filter, setFilter] = useState<NomenclatureFilter>('all');
  const [valuesToSave, setValuesToSave] = useState<NomenclatureValues>({});
  const {data: nomenclatureLines} = useGetFamilyNomenclatureValues(nomenclature, filter, valuesToSave);

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

  const handleChangeValue = useCallback((familyCode, value) => {
    if (nomenclature) {
      setValuesToSave({...nomenclature.values, [familyCode]: value});
    }
  }, [nomenclature]);

  const handleChangeOperator = useCallback(operator => {
    if (nomenclature) {
      setNomenclature({...nomenclature, operator: operator });
    }
  }, [nomenclature]);

  const handleValueChange = useCallback((value: string) => {
    if (nomenclature) {
      setNomenclature({...nomenclature, value: value === '' ? undefined : Number.parseInt(value)});
    }
  }, [nomenclature]);

  const handleGenerateIfEmptyChange = useCallback(generate_if_empty => {
    if (nomenclature) {
      setNomenclature({...nomenclature, generate_if_empty});
    }
  }, [nomenclature]);

  const handleSearchChange = useCallback(search => {
    setSearch(search);
  }, []);

  return <>
    <button onClick={open}>Open nomenclature</button>
    {isOpen && <Modal closeTitle='TODO Close' onClose={close}>
      <Modal.SectionTitle color="brand">Generating my identifiers for Families TODO</Modal.SectionTitle>
      <Modal.Title>Manage nomenclature TODO</Modal.Title>
      {nomenclature &&
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
                  />
                  <NumberInput
                    value={`${nomenclature.value}`}
                    onChange={handleValueChange}
                  />
                  <Checkbox
                    checked={nomenclature.generate_if_empty}
                    onChange={handleGenerateIfEmptyChange}
                  />
                  Generate nomenclature automatically
                </Table.Cell>
              </Table.Row>
            </Table.Body>
          </Table>
          <div style={{flexBasis: 'calc(100vh - 300px)', overflow: 'auto', width: 'calc(100vw - 160px)'}}>
            <Search searchValue={search} onSearchChange={handleSearchChange} />
              <Pagination
                  currentPage={1}
                  itemsPerPage={25}
                  totalItems={3000}
                  followPage={nextPage => console.log({nextPage})}
              />
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
                    onChange={handleChangeValue}
                    key={nomenclatureLine.code}
                  />
                  )
                )}
              </Table.Body>
            </Table>
              <Pagination
                  currentPage={1}
                  itemsPerPage={25}
                  totalItems={3000}
                  followPage={nextPage => console.log({nextPage})}
              />
          </div>
        </>}
    </Modal>}
  </>;
};

export {NomenclatureEdit};
