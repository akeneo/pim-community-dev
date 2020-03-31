import React, {useCallback, useContext, useState} from 'react';
import {useHistory} from 'react-router-dom';
import {PageHeader, PageHeaderPlaceholder} from 'akeneomeasure/shared/components/PageHeader';
import {PimView} from 'akeneomeasure/bridge/legacy/pim-view/PimView';
import {Breadcrumb} from 'akeneomeasure/shared/components/Breadcrumb';
import {BreadcrumbItem} from 'akeneomeasure/shared/components/BreadcrumbItem';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {MeasurementFamilyIllustration} from 'akeneomeasure/shared/illustrations/MeasurementFamilyIllustration';
import {HelperTitle, HelperText, Helper} from 'akeneomeasure/shared/components/Helper';
import {Link} from 'akeneomeasure/shared/components/Link';
import {NoDataSection, NoDataTitle, NoDataText} from 'akeneomeasure/shared/components/NoData';
import {useMeasurementFamilies} from 'akeneomeasure/hooks/use-measurement-families';
import {SearchBar} from 'akeneomeasure/shared/components/SearchBar';
import {
  sortMeasurementFamily,
  filterOnLabelOrCode,
  MeasurementFamilyCode,
} from 'akeneomeasure/model/measurement-family';
import {UserContext} from 'akeneomeasure/context/user-context';
import {MeasurementFamilyTable} from 'akeneomeasure/pages/list/MeasurementFamilyTable';
import {Button} from 'akeneomeasure/shared/components/Button';
import {CreateMeasurementFamily} from 'akeneomeasure/pages/create-measurement-family/CreateMeasurementFamily';
import {useToggleState} from 'akeneomeasure/shared/hooks/use-toggle-state';
import {PageContent} from 'akeneomeasure/shared/components/PageContent';
import {TablePlaceholder} from 'akeneomeasure/pages/common/Table';
import {Direction} from 'akeneomeasure/model/direction';
import {SecurityContext} from 'akeneomeasure/context/security-context';

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
  const isGranted = useContext(SecurityContext);
  const locale = useContext(UserContext)('uiLocale');
  const history = useHistory();
  const [searchValue, setSearchValue] = useState('');
  const [sortColumn, getSortDirection, toggleSortDirection] = useSorting('label');
  const [measurementFamilies] = useMeasurementFamilies();
  const [isCreateModalOpen, openCreateModal, closeCreateModal] = useToggleState(false);

  const handleModalClose = useCallback(
    (createdMeasurementFamilyCode?: MeasurementFamilyCode) => {
      closeCreateModal();
      if (undefined !== createdMeasurementFamilyCode) {
        history.push(`/${createdMeasurementFamilyCode}`);
      }
    },
    [closeCreateModal, history]
  );

  const filteredMeasurementFamilies =
    null === measurementFamilies
      ? null
      : measurementFamilies
          .filter(filterOnLabelOrCode(searchValue, locale))
          .sort(sortMeasurementFamily(getSortDirection(sortColumn), locale, sortColumn));

  const measurementFamiliesCount = null === measurementFamilies ? 0 : measurementFamilies.length;
  const filteredMeasurementFamiliesCount =
    null === filteredMeasurementFamilies ? 0 : filteredMeasurementFamilies.length;

  return (
    <>
      {isCreateModalOpen && <CreateMeasurementFamily onClose={handleModalClose} />}

      <PageHeader
        userButtons={
          <PimView
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
            viewName="pim-measurements-user-navigation"
          />
        }
        buttons={
          isGranted('akeneo_measurements_measurement_family_create')
            ? [<Button onClick={openCreateModal}>{__('pim_common.create')}</Button>]
            : []
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
              <Link href="https://help.akeneo.com/pim/articles/what-about-measurements.html" target="_blank">
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
              <Link onClick={openCreateModal}>{__('measurements.family.no_data.link')}</Link>
            </NoDataText>
          </NoDataSection>
        )}
        {null !== filteredMeasurementFamilies && 0 < measurementFamiliesCount && (
          <>
            <SearchBar
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
              <MeasurementFamilyTable
                measurementFamilies={filteredMeasurementFamilies}
                toggleSortDirection={toggleSortDirection}
                getSortDirection={getSortDirection}
              />
            )}
          </>
        )}
      </PageContent>
    </>
  );
};

export {List};
