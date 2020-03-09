import React, {useCallback, useContext, useState} from 'react';
import styled from 'styled-components';
import {PageHeader} from 'akeneomeasure/shared/components/PageHeader';
import {PimView} from 'akeneomeasure/bridge/legacy/pim-view/PimView';
import {Breadcrumb} from 'akeneomeasure/shared/components/Breadcrumb';
import {BreadcrumbItem} from 'akeneomeasure/shared/components/BreadcrumbItem';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {MeasurementFamily as MeasurementFamilyIllustration} from 'akeneomeasure/shared/illustrations/MeasurementFamily';
import {HelperTitle, HelperText, Helper} from 'akeneomeasure/shared/components/Helper';
import {Link} from 'akeneomeasure/shared/components/Link';
import {NoDataSection, NoDataTitle, NoDataText} from 'akeneomeasure/shared/components/NoData';
import {useMeasurementFamilies} from 'akeneomeasure/hooks/use-measurement-families';
import {SearchBar} from 'akeneomeasure/shared/components/SearchBar';
import {filterMeasurementFamily, sortMeasurementFamily} from 'akeneomeasure/model/measurement-family';
import {UserContext} from 'akeneomeasure/context/user-context';
import {Direction} from 'akeneomeasure/shared/components/Caret';
import {Table} from 'akeneomeasure/pages/list/Table';
import {Button} from 'akeneomeasure/shared/components/Button';
import {CreateMeasurementFamily} from 'akeneomeasure/pages/create-measurement-family/CreateMeasurementFamily';

const Container = styled.div``;
const PageContent = styled.div`
  padding: 0 40px;
`;

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

const StickySearchBar = styled(SearchBar)`
  position: sticky;
  top: 126px;
`;

const useSorting = (
  defaultColumn: string
): [string, (columnCode: string) => Direction, (columnCode: string) => void] => {
  const [sortDirection, setSortDirection] = useState(Direction.Ascending);
  const [sortColumn, setSortColumn] = useState(defaultColumn);

  return [
    sortColumn,
    (columnCode: string): Direction => (sortColumn === columnCode ? sortDirection : Direction.Descending),
    (columnCode: string) => {
      const currentSortDirection = sortColumn === columnCode ? sortDirection : Direction.Descending;

      setSortDirection(Direction.Ascending === currentSortDirection ? Direction.Descending : Direction.Ascending);
      setSortColumn(columnCode);
    },
  ];
};

const List = () => {
  const __ = useContext(TranslateContext);
  const [searchValue, setSearchValue] = useState('');
  const [sortColumn, getSortDirection, toggleSortDirection] = useSorting('label');
  const measurementFamilies = useMeasurementFamilies();
  const locale = useContext(UserContext)('uiLocale');

  const [createMeasurementFamilyModalIsOpen, setCreateMeasurementFamilyModalIsOpen] = useState<boolean>(false);
  const handleCreateMeasurementFamilyClick = useCallback(() => {
    setCreateMeasurementFamilyModalIsOpen(true);
  }, [setCreateMeasurementFamilyModalIsOpen]);

  const filteredMeasurementFamilies =
    null === measurementFamilies
      ? null
      : measurementFamilies
          .filter(measurementFamily => filterMeasurementFamily(measurementFamily, searchValue, locale))
          .sort(sortMeasurementFamily(getSortDirection(sortColumn), locale, sortColumn));

  const measurementFamiliesCount = null === measurementFamilies ? 0 : measurementFamilies.length;
  const filteredMeasurementFamiliesCount =
    null === filteredMeasurementFamilies ? 0 : filteredMeasurementFamilies.length;

  return (
    <>
      {createMeasurementFamilyModalIsOpen && <CreateMeasurementFamily/>}

      <PageHeader
        userButtons={
          <PimView
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
            viewName="pim-measurements-user-navigation"
          />
        }
        buttons={[
          <Button classNames={['AknButton--apply']} onClick={handleCreateMeasurementFamilyClick}>Create</Button>
        ]}
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
        {null !== filteredMeasurementFamilies && 0 < measurementFamiliesCount && (
          <Container>
            <StickySearchBar
              count={filteredMeasurementFamiliesCount}
              searchValue={searchValue}
              onSearchChange={setSearchValue}
            />
            {0 === filteredMeasurementFamiliesCount && (
              <NoDataSection>
                <MeasurementFamilyIllustration size={256} />
                <NoDataTitle>{__('measurements.family.no_result.title')}</NoDataTitle>
              </NoDataSection>
            )}
            {0 < filteredMeasurementFamiliesCount && (
              <Table
                measurementFamilies={filteredMeasurementFamilies}
                toggleSortDirection={toggleSortDirection}
                getSortDirection={getSortDirection}
              />
            )}
          </Container>
        )}
      </PageContent>
    </>
  );
};

export {List};
