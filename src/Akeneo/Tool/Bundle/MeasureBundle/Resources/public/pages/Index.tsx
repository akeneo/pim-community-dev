import React, {useContext} from 'react';
import styled from 'styled-components';
import {PageHeader} from 'akeneomeasure/shared/components/PageHeader';
import {PageContent} from 'akeneomeasure/shared/components/PageContent';
import {PimView} from 'akeneomeasure/shared/components/pim-view/PimView';
import {Breadcrumb} from 'akeneomeasure/shared/components/Breadcrumb';
import {BreadcrumbItem} from 'akeneomeasure/shared/components/BreadcrumbItem';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {akeneoTheme} from 'akeneomeasure/shared/theme';
import {MeasurementFamily as MeasurementFamilyIllustration} from 'akeneomeasure/shared/illustrations/measurement-family';
import {HelperTitle, HelperText, Helper} from 'akeneomeasure/shared/components/helper';
import {Link} from 'akeneomeasure/shared/components/link';
import {NoDataSection, NoDataTitle, NoDataText} from 'akeneomeasure/shared/components/no-data';
import {useMeasurementFamilies} from 'akeneomeasure/hooks/use-measurement-families';
import {MeasurementFamilyRow} from 'akeneomeasure/pages/index/measurement-family-row';
import {SearchBar} from 'akeneomeasure/shared/components/search-bar';
import {filterMeasurementFamily, sortMeasurementFamily} from 'akeneomeasure/model/measurement-family';
import {UserContext} from 'akeneomeasure/context/user-context';
import {Direction, Caret} from 'akeneomeasure/shared/components/Caret';

const Container = styled.div``;

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

const PageHeaderPlaceholder = styled.div`
  width: 200px;
  height: 34px;
`;

const TablePlaceholder = styled.div`
  display: grid;
  grid-row-gap: 10px;

  > div {
    height: 54px;
  }
`;

export const Index = () => {
  const __ = useContext(TranslateContext);
  const [searchValue, setSearchValue] = React.useState('');
  const [sortDirection, setSortDirection] = React.useState(Direction.Ascending);
  const measurementFamilies = useMeasurementFamilies();
  const locale = useContext(UserContext)('uiLocale');

  const filteredMeasurementFamilies =
    null === measurementFamilies
      ? null
      : measurementFamilies
          .filter(measurementFamily => filterMeasurementFamily(measurementFamily, searchValue, locale))
          .sort(sortMeasurementFamily(sortDirection, locale));

  const measurementFamiliesCount = null === measurementFamilies ? 0 : measurementFamilies.length;
  const filteredMeasurementFamiliesCount =
    null === filteredMeasurementFamilies ? 0 : filteredMeasurementFamilies.length;

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
        {null === filteredMeasurementFamilies ? (
          <div className={`AknLoadingPlaceHolderContainer`}>
            <PageHeaderPlaceholder />
          </div>
        ) : (
          __(
            'measurements.family.result_count',
            {itemsCount: measurementFamiliesCount.toString()},
            measurementFamiliesCount
          )
        )}
      </PageHeader>

      <PageContent>
        <Helper>
          <MeasurementFamilyIllustration size={80} />
          <HelperTitle>
            👋 {__('measurements.helper.title')}
            <HelperText>
              {__('measurements.helper.text')}
              <br />
              <Link href="https://help.akeneo.com/" target="_blank">
                {__('measurements.helper.link')}
              </Link>
            </HelperText>
          </HelperTitle>
        </Helper>
        {null === filteredMeasurementFamilies && (
          <TablePlaceholder className={`AknLoadingPlaceHolderContainer`}>
            {[...Array(5)].map((_e, i) => (
              <div key={i} />
            ))}
          </TablePlaceholder>
        )}
        {null !== filteredMeasurementFamilies && 0 === measurementFamiliesCount && (
          <NoDataSection>
            <MeasurementFamilyIllustration size={256} />
            <NoDataTitle>{__('measurements.family.no_data.title')}</NoDataTitle>
            <NoDataText>
              <Link
                onClick={() => {
                  // TODO connect create button
                }}
              >
                {__('measurements.family.no_data.link')}
              </Link>
            </NoDataText>
          </NoDataSection>
        )}
        {null !== filteredMeasurementFamilies && (
          <Container>
            <SearchBar
              count={filteredMeasurementFamiliesCount}
              searchValue={searchValue}
              onSearchChange={(newSearchValue: string) => {
                setSearchValue(newSearchValue);
              }}
            />
            {0 === filteredMeasurementFamiliesCount && (
              <NoDataSection>
                <MeasurementFamilyIllustration size={256} />
                <NoDataTitle>{__('measurements.family.no_result.title')}</NoDataTitle>
              </NoDataSection>
            )}
            {0 < filteredMeasurementFamiliesCount && (
              <Table>
                <TableHeader>
                  <tr>
                    <td>
                      {__('measurements.list.header.label')}{' '}
                      <Caret direction={sortDirection} onChange={newDirection => setSortDirection(newDirection)} />
                    </td>
                    <td>{__('measurements.list.header.code')}</td>
                    <td>{__('measurements.list.header.standard_unit')}</td>
                    <td>{__('measurements.list.header.unit_count')}</td>
                  </tr>
                </TableHeader>
                <TableBody>
                  {filteredMeasurementFamilies.map(measurementFamily => (
                    <MeasurementFamilyRow key={measurementFamily.code} measurementFamily={measurementFamily} />
                  ))}
                </TableBody>
              </Table>
            )}
          </Container>
        )}
      </PageContent>
    </>
  );
};
