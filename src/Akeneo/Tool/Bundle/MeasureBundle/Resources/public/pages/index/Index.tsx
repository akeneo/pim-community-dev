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
import {ResultCount} from 'akeneomeasure/shared/components/result-count';
import {akeneoTheme} from 'akeneomeasure/shared/theme';
import {Search as SearchIcon} from 'akeneomeasure/shared/icons/search';
import {MeasurementFamily as MeasurementFamilyIllustration} from 'akeneomeasure/shared/illustrations/measurement-family';
import {HelperTitle, HelperText, Helper} from 'akeneomeasure/shared/components/helper';
import {Link} from 'akeneomeasure/shared/components/link';

const List = styled.div``;

const SearchBar = styled.div`
  display: flex;
  justify-content: space-between;
  border-bottom: 1px solid ${akeneoTheme.color.grey100};
  padding: 13px 0;
  margin: 20px 0;
`;
const SearchContainer = styled.div`
  display: flex;
  align-items: center;
`;
const SearchInput = styled.input`
  border: none;
  width: 180px;
  margin-left: 5px;
  color: ${akeneoTheme.color.grey120};
  outline: none;

  ::placeholder {
    color: ${akeneoTheme.color.grey120};
  }
`;

const Table = styled.table`
  width: 100%;
  color: ${akeneoTheme.color.grey140};
  border-collapse: collapse;

  td {
    width: 25%;
  }
`;
const TableHeader = styled.thead`
  tr {
    height: 43px;
    border-bottom: 1px solid ${akeneoTheme.color.grey120};
  }
`;
const TableBody = styled.tbody``;
const Row = styled.tr`
  height: 54px;
`;
const MeasurementFamilyLabelCell = styled.td`
  color: ${akeneoTheme.color.purple100};
  font-style: italic;
  font-weight: bold;
`;

const fetchMeasurementFamilies = (): Promise<MeasurementFamily[]> =>
  Promise.resolve([
    {
      code: 'AREA',
      labels: {en_US: 'Area', fr_FR: 'Aire'},
      standard_unit_code: 'SQUARE_METER',
      units: [
        {
          code: 'SQUARE_METER',
          labels: {en_US: 'Square meter', fr_FR: 'metre carre'},
          symbol: 'm2',
          convert_from_standard: [{operator: 'mul', value: '1'}],
        },
      ],
    },
    {
      code: 'LENGTH',
      labels: {en_US: 'Length', fr_FR: 'Longueur'},
      standard_unit_code: 'KILOMETER',
      units: [
        {
          code: 'KILOMETER',
          labels: {en_US: 'Kilometre', fr_FR: 'Kilomètre'},
          symbol: 'm2',
          convert_from_standard: [{operator: 'mul', value: '1'}],
        },
      ],
    },
    {
      code: 'OTHER',
      labels: {en_US: 'Other', fr_FR: 'Autre'},
      standard_unit_code: 'ANOTHER_ONE',
      units: [
        {
          code: 'ANOTHER_ONE',
          labels: {en_US: 'Other unit', fr_FR: 'Autre unité'},
          symbol: 'm2',
          convert_from_standard: [{operator: 'mul', value: '1'}],
        },
      ],
    },
  ]);

const useMeasurementFamilies = () => {
  const [measurementFamilies, setMeasurementFamilies] = useState<MeasurementFamily[] | null>(null);

  useEffect(() => {
    (async () => setMeasurementFamilies(await fetchMeasurementFamilies()))();
  }, []);

  return measurementFamilies;
};

type MeasurementFamilyRowProps = {
  measurementFamily: MeasurementFamily;
};

const MeasurementFamilyRow = ({measurementFamily}: MeasurementFamilyRowProps) => {
  const locale = useContext(UserContext)('uiLocale');

  return (
    <Row>
      <MeasurementFamilyLabelCell>{getMeasurementFamilyLabel(measurementFamily, locale)}</MeasurementFamilyLabelCell>
      <td>{measurementFamily.code}</td>
      <td>{getStandardUnitLabel(measurementFamily, locale)}</td>
      <td>{measurementFamily.units.length}</td>
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
        {__(
          'measurements.families',
          {itemsCount: (measurementFamilies ? measurementFamilies.length : 0).toString()},
          measurementFamilies ? measurementFamilies.length : 0
        )}
      </PageHeader>

      <PageContent>
        <Helper>
          <MeasurementFamilyIllustration size={80} />
          <HelperTitle>
            👋 {__('measurements.helper.title')}
            <HelperText>
              {__('measurements.helper.message')}
              <br />
              <Link href="https://help.akeneo.com/" target="_blank">
                {__('measurements.helper.link')}
              </Link>
            </HelperText>
          </HelperTitle>
        </Helper>
        <List>
          <SearchBar>
            <SearchContainer>
              <SearchIcon />
              <SearchInput placeholder={__('measurements.search.placeholder')} />
            </SearchContainer>
            <ResultCount count={measurementFamilies ? measurementFamilies.length : 0} />
          </SearchBar>
          <Table>
            <TableHeader>
              <tr>
                <td>{__('measurements.list.header.label')}</td>
                <td>{__('measurements.list.header.code')}</td>
                <td>{__('measurements.list.header.standard_unit')}</td>
                <td>{__('measurements.list.header.unit_count')}</td>
              </tr>
            </TableHeader>
            <TableBody>
              {measurementFamilies?.map(measurementFamily => (
                <MeasurementFamilyRow key={measurementFamily.code} measurementFamily={measurementFamily} />
              ))}
            </TableBody>
          </Table>
        </List>
      </PageContent>
    </>
  );
};
