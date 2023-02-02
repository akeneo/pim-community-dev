import React, {FC, useCallback, useState, useEffect, useMemo} from 'react';
import {
  Checkbox,
  Modal,
  NumberInput,
  Search,
  SectionTitle,
  Table,
  TextInput,
  useBooleanState
} from 'akeneo-design-system';
import {useGetFamilies} from '../hooks/useGetFamilies';
import {Styled} from './Styled';
import {getLabel} from '@akeneo-pim-community/shared';
import {useGetNomenclature} from '../hooks';
import {OperatorSelector} from './OperatorSelector';
import {Nomenclature, Operator} from '../models';

type NomenclatureEditProps = {};

const NomenclatureEdit: FC<NomenclatureEditProps> = () => {
  const [isOpen, open, close] = useBooleanState();
  const [nomenclature, setNomenclature] = useState<Nomenclature | undefined>(undefined);
  const {nomenclature: fetchedNomenclature} = useGetNomenclature();
  const [search, setSearch] = useState<string>('');

  useEffect(() => {
    if (fetchedNomenclature) {
      setNomenclature(fetchedNomenclature);
    }
  }, [fetchedNomenclature]);

  const {data: families} = useGetFamilies({
    page: 1,
    search,
  });

  const handleChangeValue = useCallback((familyCode, value) => {
    if (nomenclature) {
      const values = nomenclature.values;
      values[familyCode] = value;
      setNomenclature({...nomenclature, values });
    }
  }, [nomenclature]);

  const handleChangeOperator = useCallback(operator => {
    if (nomenclature) {
      setNomenclature({...nomenclature, operator: operator as (Operator.EQUALS | Operator.LOWER_OR_EQUAL_THAN)});
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

  const getPlaceholder = useCallback(familyCode => {
    if (nomenclature && nomenclature.generate_if_empty) {
      return familyCode.substr(0, nomenclature.value || 0);
    }
  }, [nomenclature?.generate_if_empty, nomenclature?.value])

  const getValue = useCallback(familyCode => {
    return nomenclature?.values[familyCode] || '';
  }, [nomenclature]);

  const isValid = useCallback(familyCode => {
    const value = getValue(familyCode);
    if (nomenclature && nomenclature.value && nomenclature.operator) {
      if (nomenclature.generate_if_empty && value === '') {
        return true;
      }
      if (nomenclature.operator === Operator.EQUALS && value.length !== nomenclature.value) {
        return false;
      }
      if (nomenclature.operator === Operator.LOWER_OR_EQUAL_THAN && value.length > nomenclature.value) {
        return false;
      }
    }
    return true;
  }, [nomenclature]);

  return <>
    <button onClick={open}>Open nomenclature</button>
    {isOpen && <Modal closeTitle='TODO Close' onClose={close}>
      <Modal.SectionTitle color="brand">Generating my identifiers for Families TODO</Modal.SectionTitle>
      <Modal.Title>Manage nomenclature TODO</Modal.Title>
      {nomenclature &&
        <>
          <SectionTitle><SectionTitle.Title>Families TODO</SectionTitle.Title></SectionTitle>
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
            <Table>
              <Table.Header>
                <Table.HeaderCell>Label TODO</Table.HeaderCell>
                <Table.HeaderCell>Code TODO</Table.HeaderCell>
                <Table.HeaderCell>Nomenclature TODO</Table.HeaderCell>
              </Table.Header>
              <Table.Body>
                {families && families.map(family => <Table.Row key={family.code}>
                  <Styled.TitleCell>
                    {getLabel(family.labels, 'en_US', family.code)}
                  </Styled.TitleCell>
                  <Table.Cell>
                    {family.code}
                  </Table.Cell>
                  <Table.Cell>
                    <TextInput
                      value={getValue(family.code)}
                      invalid={!isValid(family.code)}
                      readOnly={false}
                      onChange={(value) => handleChangeValue(family.code, value)}
                      placeholder={getPlaceholder(family.code)}
                    />
                  </Table.Cell>
                </Table.Row>
                )}
              </Table.Body>
            </Table>
          </div>
        </>}
    </Modal>}
  </>;
};

export {NomenclatureEdit};
