import React, {useContext, useEffect, useState} from 'react';
import styled from 'styled-components';
import {PageHeader} from 'akeneomeasure/shared/components/PageHeader';
import {PageContent} from 'akeneomeasure/shared/components/PageContent';
import {PimView} from 'akeneomeasure/shared/components/pim-view/PimView';
import {Breadcrumb} from 'akeneomeasure/shared/components/Breadcrumb';
import {BreadcrumbItem} from 'akeneomeasure/shared/components/BreadcrumbItem';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {
  MeasurementFamily,
  getMeasurementFamilyLabel,
  getStandardUnitLabel,
} from 'akeneomeasure/model/measurement-family';
import {UserContext} from 'akeneomeasure/context/user-context';

const Helper = styled.div``;
const List = styled.div``;
const SearchBar = styled.div``;
const TableHeader = styled.div``;
const TableBody = styled.div``;
const Table = styled.div``;
const SearchInput = styled.div``;
const ResultCount = styled.div``;

const fetchMeasurementFamilies = (): Promise<MeasurementFamily[]> =>
  Promise.resolve([
    {
      code: 'AREA',
      labels: {en_US: 'Area'},
      standard_unit_code: 'SQUARE_METER',
      units: [
        {
          code: 'SQUARE_METER',
          labels: {en_US: 'Square meter'},
          symbol: 'm2',
          convert_from_standard: [{operator: 'mul', value: '1'}],
        },
      ],
    },
    {
      code: 'LENGTH',
      labels: {en_US: 'Length'},
      standard_unit_code: 'SQUARE_METER',
      units: [
        {
          code: 'SQUARE_METER',
          labels: {en_US: 'Square meter'},
          symbol: 'm2',
          convert_from_standard: [{operator: 'mul', value: '1'}],
        },
      ],
    },
    {
      code: 'OTHER',
      labels: {en_US: 'Other'},
      standard_unit_code: 'SQUARE_METER',
      units: [
        {
          code: 'SQUARE_METER',
          labels: {en_US: 'Square meter'},
          symbol: 'm2',
          convert_from_standard: [{operator: 'mul', value: '1'}],
        },
      ],
    },
  ]);

const useMeasurementFamilies = () => {
  const [measurementFamilies, setMeasurementFamilies] = useState<MeasurementFamily[] | null>(null);

  useEffect(() => {
    (async () => {
      const data = await fetchMeasurementFamilies();
      setMeasurementFamilies(data);
    })();
  });

  return measurementFamilies;
};

const Row = styled.div`
  display: flex;
`;

type MeasurementFamilyRowProps = {
  measurementFamily: MeasurementFamily;
};

const MeasurementFamilyRow = ({measurementFamily}: MeasurementFamilyRowProps) => {
  const locale = useContext(UserContext)('uiLocale');

  return (
    <Row>
      <div>{getMeasurementFamilyLabel(measurementFamily, locale)}</div>
      <div>{measurementFamily.code}</div>
      <div>{getStandardUnitLabel(measurementFamily, locale)}</div>
      <div>{measurementFamily.units.length}</div>
    </Row>
  );
};

export const Index = () => {
  const __ = useContext(TranslateContext);

  const measurementFamilies = useMeasurementFamilies();

  return (
    <>
      <PageHeader
        userButtons={
          <PimView
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
            viewName="pim-measurements-user-navigation"
          />
        }
        breadcrumb={
          <Breadcrumb>
            <BreadcrumbItem>{__('pim_menu.tab.settings')}</BreadcrumbItem>
            <BreadcrumbItem>{__('pim_menu.item.measurements')}</BreadcrumbItem>
          </Breadcrumb>
        }
      >
        {__('measurements.families', {itemsCount: '0'}, 0)}
      </PageHeader>

      <PageContent>
        <Helper>🍆</Helper>
        <List>
          <SearchBar>
            <SearchInput></SearchInput>
            <ResultCount></ResultCount>
          </SearchBar>
          <Table>
            <TableHeader></TableHeader>
            <TableBody>
              {measurementFamilies?.map(measurementFamily => (
                <MeasurementFamilyRow measurementFamily={measurementFamily} />
              ))}
            </TableBody>
          </Table>
        </List>
      </PageContent>
    </>
  );
};
