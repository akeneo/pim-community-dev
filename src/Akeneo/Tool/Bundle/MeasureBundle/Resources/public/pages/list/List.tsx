import React, {useCallback, useState} from 'react';
import {useHistory} from 'react-router-dom';
import {MeasurementIllustration, Link, Button, Information, Breadcrumb, useBooleanState} from 'akeneo-design-system';
import {PageHeader, PageHeaderPlaceholder} from '../../shared/components/PageHeader';
import {useMeasurementFamilies} from '../../hooks/use-measurement-families';
import {sortMeasurementFamily, filterOnLabelOrCode, MeasurementFamilyCode} from '../../model/measurement-family';
import {MeasurementFamilyTable} from '../list/MeasurementFamilyTable';
import {CreateMeasurementFamily} from '../create-measurement-family/CreateMeasurementFamily';
import {TablePlaceholder} from '../common/Table';
import {Direction} from '../../model/direction';
import {
  SearchBar,
  NoDataSection,
  NoDataTitle,
  NoDataText,
  PageContent,
  useTranslate,
  useUserContext,
  useSecurity,
  useRoute,
  PimView,
} from '@akeneo-pim-community/shared';

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
  const __ = useTranslate();
  const {isGranted} = useSecurity();
  const locale = useUserContext().get('uiLocale');
  const history = useHistory();
  const [searchValue, setSearchValue] = useState('');
  const [sortColumn, getSortDirection, toggleSortDirection] = useSorting('label');
  const [measurementFamilies] = useMeasurementFamilies();
  const [isCreateModalOpen, openCreateModal, closeCreateModal] = useBooleanState(false);
  const settingsHref = useRoute('pim_settings_index');

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
      <CreateMeasurementFamily isOpen={isCreateModalOpen} onClose={handleModalClose} />
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
            <Breadcrumb.Step href={`#${settingsHref}`}>{__('pim_menu.tab.settings')}</Breadcrumb.Step>
            <Breadcrumb.Step>{__('pim_menu.item.measurements')}</Breadcrumb.Step>
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
        <Information illustration={<MeasurementIllustration />} title={`👋  ${__('measurements.helper.title')}`}>
          <p>{__('measurements.helper.text')}</p>
          <Link href="https://help.akeneo.com/pim/articles/what-about-measurements.html" target="_blank">
            {__('measurements.helper.link')}
          </Link>
        </Information>
        {null === filteredMeasurementFamilies && (
          <TablePlaceholder className={`AknLoadingPlaceHolderContainer`}>
            {[...Array(5)].map((_e, i) => (
              <div key={i} />
            ))}
          </TablePlaceholder>
        )}
        {null !== filteredMeasurementFamilies && 0 === measurementFamiliesCount && (
          <NoDataSection>
            <MeasurementIllustration />
            <NoDataTitle>{__('measurements.family.no_data.title')}</NoDataTitle>
            <NoDataText>
              <Link onClick={openCreateModal}>{__('measurements.family.no_data.link')}</Link>
            </NoDataText>
          </NoDataSection>
        )}
        {null !== filteredMeasurementFamilies && 0 < measurementFamiliesCount && (
          <>
            <SearchBar
              placeholder={__('measurements.search.placeholder')}
              count={filteredMeasurementFamiliesCount}
              searchValue={searchValue}
              onSearchChange={setSearchValue}
            />
            {0 === filteredMeasurementFamiliesCount && (
              <NoDataSection>
                <MeasurementIllustration />
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
