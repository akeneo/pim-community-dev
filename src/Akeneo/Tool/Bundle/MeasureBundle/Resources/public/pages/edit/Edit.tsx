import React, {useState, useContext, useCallback, useEffect} from 'react';
import {useParams, useHistory} from 'react-router-dom';
import styled from 'styled-components';
import {useMeasurementFamily} from 'akeneomeasure/hooks/use-measurement-family';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {UnitTab} from 'akeneomeasure/pages/edit/UnitTab';
import {PropertyTab} from 'akeneomeasure/pages/edit/PropertyTab';
import {PageHeader, PageHeaderPlaceholder} from 'akeneomeasure/shared/components/PageHeader';
import {PimView} from 'akeneomeasure/bridge/legacy/pim-view/PimView';
import {Breadcrumb} from 'akeneomeasure/shared/components/Breadcrumb';
import {BreadcrumbItem} from 'akeneomeasure/shared/components/BreadcrumbItem';
import {Button} from 'akeneomeasure/shared/components/Button';
import {getMeasurementFamilyLabel, addUnit, MeasurementFamily} from 'akeneomeasure/model/measurement-family';
import {Unit} from 'akeneomeasure/model/unit';
import {UserContext} from 'akeneomeasure/context/user-context';
import {PageContent} from 'akeneomeasure/shared/components/PageContent';
import {
  SecondaryActionsDropdownButton,
  DropdownLink,
} from 'akeneomeasure/shared/components/SecondaryActionsDropdownButton';
import {NotificationLevel, NotifyContext} from 'akeneomeasure/context/notify-context';
import {ValidationError, filterErrors} from 'akeneomeasure/model/validation-error';
import {useSaveMeasurementFamilySaver} from 'akeneomeasure/pages/edit/hooks/use-save-measurement-family-saver';
import {ErrorBadge} from 'akeneomeasure/shared/components/ErrorBadge';
import {useToggleState} from 'akeneomeasure/shared/hooks/use-toggle-state';
import {CreateUnit} from 'akeneomeasure/pages/create-unit/CreateUnit';
import {SubsectionHelper, HELPER_LEVEL_WARNING} from 'akeneomeasure/shared/components/SubsectionHelper';
import {useUnsavedChanges} from 'akeneomeasure/shared/hooks/use-unsaved-changes';
import {UnsavedChanges} from 'akeneomeasure/shared/components/UnsavedChanges';
import {UnsavedChangesContext} from 'akeneomeasure/context/unsaved-changes-context';
import {
  useMeasurementFamilyRemover,
  MeasurementFamilyRemoverResult,
} from 'akeneomeasure/hooks/use-measurement-family-remover';
import {ConfirmDeleteModal} from 'akeneomeasure/shared/components/ConfirmDeleteModal';

enum Tab {
  Units = 'units',
  Properties = 'properties',
}

const Container = styled.div`
  /* 70 = TabContainer height + margin */
  height: calc(100% - 70px);
  display: flex;
`;

const TabsContainer = styled.div`
  margin-bottom: 20px;
`;

const Tabs = styled.div`
  display: flex;
  width: 100%;
  height: 50px;
  border-bottom: 1px solid ${props => props.theme.color.grey80};
`;

const TabSelector = styled.div<{isActive: boolean}>`
  width: 90px;
  padding: 13px 0;
  cursor: pointer;
  font-size: ${props => props.theme.fontSize.big};
  color: ${props => (props.isActive ? props.theme.color.purple100 : 'inherit')};
  border-bottom: 3px solid ${props => (props.isActive ? props.theme.color.purple100 : 'transparent')};
  display: flex;
  align-items: baseline;

  > :last-child {
    margin-left: 5px;
  }
`;

const hasTabErrors = (tab: Tab, errors: ValidationError[]): boolean => {
  const unitsErrorCount = filterErrors(errors, 'units').length;

  switch (tab) {
    case Tab.Units:
      return 0 < unitsErrorCount;
    case Tab.Properties:
      return 0 < errors.length - unitsErrorCount;
    default:
      return false;
  }
};

const Edit = () => {
  const __ = useContext(TranslateContext);
  const history = useHistory();
  const locale = useContext(UserContext)('uiLocale');
  const {measurementFamilyCode} = useParams() as {measurementFamilyCode: string};
  const [currentTab, setCurrentTab] = useState<Tab>(Tab.Units);
  const [measurementFamily, setMeasurementFamily] = useMeasurementFamily(measurementFamilyCode);
  const [errors, setErrors] = useState<ValidationError[]>([]);
  const notify = useContext(NotifyContext);
  const [isAddUnitModalOpen, openAddUnitModal, closeAddUnitModal] = useToggleState(false);
  const [
    isConfirmDeleteMeasurementFamilyModalOpen,
    openConfirmDeleteMeasurementFamilyModal,
    closeConfirmDeleteMeasurementFamilyModal,
  ] = useToggleState(false);

  const {setHasUnsavedChanges} = useContext(UnsavedChangesContext);
  const [isModified, resetState] = useUnsavedChanges<MeasurementFamily | null>(
    measurementFamily,
    __('pim_ui.flash.unsaved_changes')
  );
  useEffect(() => setHasUnsavedChanges(isModified), [isModified]);

  const saveMeasurementFamily = useSaveMeasurementFamilySaver();
  const handleSaveMeasurementFamily = useCallback(async () => {
    if (null === measurementFamily) {
      return;
    }

    setErrors([]);

    try {
      const response = await saveMeasurementFamily(measurementFamily);

      switch (response.success) {
        case true:
          resetState();
          notify(NotificationLevel.SUCCESS, __('measurements.family.save.flash.success'));
          break;

        case false:
          setErrors(response.errors);
          break;
      }
    } catch (error) {
      console.error(error);
      notify(NotificationLevel.ERROR, __('measurements.family.save.flash.error'));
    }
  }, [measurementFamily, locale, saveMeasurementFamily, notify, __, setErrors, resetState]);

  const removeMeasurementFamily = useMeasurementFamilyRemover();
  const handleRemoveMeasurementFamily = useCallback(async () => {
    try {
      const response = await removeMeasurementFamily(measurementFamilyCode);

      switch (response) {
        case MeasurementFamilyRemoverResult.Success:
          notify(NotificationLevel.SUCCESS, __('measurements.family.delete.flash.success'));
          history.push('/');
          break;
        case MeasurementFamilyRemoverResult.NotFound:
        case MeasurementFamilyRemoverResult.Unprocessable:
          throw Error(`Error while deleting the measurement family: ${response}`);
      }
    } catch (error) {
      console.error(error);
      notify(NotificationLevel.ERROR, __('measurements.family.delete.flash.error'));
    }
  }, [measurementFamilyCode, removeMeasurementFamily, history, notify, __]);

  const handleNewUnit = useCallback(
    (unit: Unit) => {
      if (null === measurementFamily) {
        return;
      }

      setMeasurementFamily(addUnit(measurementFamily, unit));
    },
    [setMeasurementFamily, measurementFamily]
  );

  if (undefined === measurementFamilyCode || null === measurementFamily) {
    return null;
  }

  return (
    <>
      {isAddUnitModalOpen && (
        <CreateUnit measurementFamily={measurementFamily} onClose={closeAddUnitModal} onNewUnit={handleNewUnit} />
      )}

      {isConfirmDeleteMeasurementFamilyModalOpen && (
        <ConfirmDeleteModal
          description={__('measurements.family.delete.confirm')}
          onConfirm={handleRemoveMeasurementFamily}
          onCancel={closeConfirmDeleteMeasurementFamilyModal}
        />
      )}

      <PageHeader
        userButtons={
          <PimView
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
            viewName="pim-measurements-user-navigation"
          />
        }
        buttons={[
          ...(!measurementFamily.is_locked
            ? [
                <SecondaryActionsDropdownButton title={__('pim_common.other_actions')} key={0}>
                  <DropdownLink onClick={openConfirmDeleteMeasurementFamilyModal}>
                    {__('measurements.family.delete.button')}
                  </DropdownLink>
                </SecondaryActionsDropdownButton>,
              ]
            : []),
          <Button color="blue" outline={true} onClick={openAddUnitModal}>
            {__('measurements.unit.add')}
          </Button>,
          <Button onClick={handleSaveMeasurementFamily}>{__('pim_common.save')}</Button>,
        ]}
        breadcrumb={
          <Breadcrumb>
            <BreadcrumbItem>{__('pim_menu.tab.settings')}</BreadcrumbItem>
            <BreadcrumbItem onClick={() => history.push('/')}>{__('pim_menu.item.measurements')}</BreadcrumbItem>
          </Breadcrumb>
        }
        state={isModified && <UnsavedChanges />}
      >
        {null === measurementFamily ? (
          <div className={`AknLoadingPlaceHolderContainer`}>
            <PageHeaderPlaceholder />
          </div>
        ) : (
          <div>{getMeasurementFamilyLabel(measurementFamily, locale)}</div>
        )}
      </PageHeader>

      <PageContent>
        <TabsContainer>
          <Tabs>
            {Object.values(Tab).map((tab: Tab) => (
              <TabSelector key={tab} onClick={() => setCurrentTab(tab)} isActive={currentTab === tab}>
                {__(`measurements.family.tab.${tab}`)}
                {hasTabErrors(tab, errors) && <ErrorBadge />}
              </TabSelector>
            ))}
          </Tabs>
          {measurementFamily.is_locked && (
            <SubsectionHelper level={HELPER_LEVEL_WARNING}>{__('measurements.family.is_locked')}</SubsectionHelper>
          )}
        </TabsContainer>
        <Container>
          {currentTab === Tab.Units && (
            <UnitTab
              measurementFamily={measurementFamily}
              onMeasurementFamilyChange={setMeasurementFamily}
              errors={filterErrors(errors, 'units')}
            />
          )}
          {currentTab === Tab.Properties && (
            <PropertyTab
              measurementFamily={measurementFamily}
              onMeasurementFamilyChange={setMeasurementFamily}
              errors={errors}
            />
          )}
        </Container>
      </PageContent>
    </>
  );
};

export {Edit};
