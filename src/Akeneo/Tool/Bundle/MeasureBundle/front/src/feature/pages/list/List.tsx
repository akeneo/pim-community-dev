import React, {useCallback, useState} from 'react';
import {useHistory} from 'react-router-dom';
import {MeasurementIllustration, Link, Button, Breadcrumb, useBooleanState} from 'akeneo-design-system';
import {
  NoDataSection,
  NoDataTitle,
  NoDataText,
  PageContent,
  useTranslate,
  useUserContext,
  useSecurity,
  useRoute,
  PimView,
  PageHeader,
} from '@akeneo-pim-community/shared';
import {useMeasurementFamilies} from '../../hooks/use-measurement-families';
import {sortMeasurementFamily, filterOnLabelOrCode, MeasurementFamilyCode} from '../../model/measurement-family';
import {MeasurementFamilyTable} from '../list/MeasurementFamilyTable';
import {CreateMeasurementFamily} from '../create-measurement-family/CreateMeasurementFamily';
import {TablePlaceholder} from '../common/Table';
import {Direction} from '../../model/direction';
import {MeasurementFamilySearchBar} from './MeasurementFamilySearchBar';

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
  const translate = useTranslate();
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
      <PageHeader showPlaceholder={null === filteredMeasurementFamilies}>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href={`#${settingsHref}`}>{translate('pim_menu.tab.settings')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.measurements')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
            viewName="pim-measurements-user-navigation"
          />
        </PageHeader.UserActions>
        <PageHeader.Actions>
          {isGranted('akeneo_measurements_measurement_family_create') && (
            <Button onClick={openCreateModal}>{translate('pim_common.create')}</Button>
          )}
        </PageHeader.Actions>
        <PageHeader.Title>
          {translate(
            'measurements.family.result_count',
            {itemsCount: filteredMeasurementFamiliesCount.toString()},
            filteredMeasurementFamiliesCount
          )}
        </PageHeader.Title>
      </PageHeader>
      <PageContent>
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
            <NoDataTitle>{translate('measurements.family.no_data.title')}</NoDataTitle>
            <NoDataText>
              <Link onClick={openCreateModal}>{translate('measurements.family.no_data.link')}</Link>
            </NoDataText>
          </NoDataSection>
        )}
        {null !== filteredMeasurementFamilies && 0 < measurementFamiliesCount && (
          <>
            <MeasurementFamilySearchBar
              searchValue={searchValue}
              onSearchChange={setSearchValue}
              resultNumber={filteredMeasurementFamiliesCount}
            />
            {0 === filteredMeasurementFamiliesCount && (
              <NoDataSection>
                <MeasurementIllustration />
                <NoDataTitle>{translate('pim_common.no_search_result')}</NoDataTitle>
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
