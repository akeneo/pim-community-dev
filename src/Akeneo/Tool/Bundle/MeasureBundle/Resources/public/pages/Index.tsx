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
          'measurements.family.result_count',
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
              {__('measurements.helper.text')}
              <br />
              <Link href="https://help.akeneo.com/" target="_blank">
                {__('measurements.helper.link')}
              </Link>
            </HelperText>
          </HelperTitle>
        </Helper>
        {null === measurementFamilies || measurementFamilies.length === 0 ? (
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
        ) : (
          <Container>
            <SearchBar count={null === measurementFamilies ? null : measurementFamilies.length} />
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
          </Container>
        )}
      </PageContent>
    </>
  );
};
