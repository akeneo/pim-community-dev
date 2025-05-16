import React, {useCallback, useContext, useEffect, useState} from 'react';
import {useHistory, useParams, Prompt} from 'react-router-dom';
import styled from 'styled-components';
import {Helper, Button, Breadcrumb, useBooleanState, Pill} from 'akeneo-design-system';
import {
  useTranslate,
  useNotify,
  NotificationLevel,
  useUserContext,
  useSecurity,
  useRoute,
  useRouter,
  filterErrors,
  ValidationError,
  partitionErrors,
  FullScreenError,
  PageContent,
  DeleteModal,
  PimView,
  PageHeader,
  SecondaryActions,
  UnsavedChanges,
} from '@akeneo-pim-community/shared';
import {useMeasurementFamily} from '../../hooks/use-measurement-family';
import {UnitTab} from './unit-tab';
import {PropertyTab} from './PropertyTab';
import {addUnit, getMeasurementFamilyLabel, MeasurementFamily} from '../../model/measurement-family';
import {Unit, UnitCode} from '../../model/unit';
import {useSaveMeasurementFamilySaver} from './hooks/use-save-measurement-family-saver';
import {CreateUnit} from '../create-unit/CreateUnit';
import {useUnsavedChanges} from '../../shared/hooks/use-unsaved-changes';
import {UnsavedChangesContext} from '../../context/unsaved-changes-context';
import {useMeasurementFamilyRemover, MeasurementFamilyRemoverResult} from '../../hooks/use-measurement-family-remover';
import {ConfigContext} from '../../context/config-context';

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

const Errors = ({errors}: {errors: ValidationError[]}) => {
  if (0 === errors.length) {
    return null;
  }

  return (
    <>
      {errors.map((error: ValidationError, index: number) => (
        <Helper level="error" key={index}>
          {error.message}
        </Helper>
      ))}
    </>
  );
};

const Edit = () => {
  const translate = useTranslate();
  const notify = useNotify();
  const history = useHistory();
  const locale = useUserContext().get('uiLocale');
  const {isGranted} = useSecurity();
  const config = useContext(ConfigContext);
  const {measurementFamilyCode} = useParams() as {measurementFamilyCode: string};
  const [currentTab, setCurrentTab] = useState<Tab>(Tab.Units);
  const [measurementFamily, setMeasurementFamily] = useMeasurementFamily(measurementFamilyCode);
  const [selectedUnitCode, selectUnitCode] = useState<UnitCode | null>(null);
  const [errors, setErrors] = useState<ValidationError[]>([]);
  const [isAddUnitModalOpen, openAddUnitModal, closeAddUnitModal] = useBooleanState(false);
  const [
    isConfirmDeleteMeasurementFamilyModalOpen,
    openConfirmDeleteMeasurementFamilyModal,
    closeConfirmDeleteMeasurementFamilyModal,
  ] = useBooleanState(false);
  const settingsHref = useRoute('pim_settings_index');
  const router = useRouter();

  const {setHasUnsavedChanges} = useContext(UnsavedChangesContext);
  const [isModified, resetState] = useUnsavedChanges<MeasurementFamily | null>(
    measurementFamily,
    translate('pim_ui.flash.unsaved_changes')
  );
  useEffect(() => {
    setHasUnsavedChanges(isModified);
  }, [isModified, setHasUnsavedChanges]);

  // If the measurement family code changes, we select the standard unit code by default
  useEffect(() => {
    if (undefined === measurementFamily?.code) {
      return;
    }

    selectUnitCode(measurementFamily.standard_unit_code);
  }, [measurementFamily?.code, measurementFamily?.standard_unit_code]);

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
          notify(NotificationLevel.SUCCESS, translate('measurements.family.save.flash.success'));
          break;

        case false:
          setErrors(response.errors);
          break;
      }
    } catch (error) {
      console.error(error);
      notify(NotificationLevel.ERROR, translate('measurements.family.save.flash.error'));
    }
  }, [measurementFamily, saveMeasurementFamily, notify, translate, setErrors, resetState]);

  const removeMeasurementFamily = useMeasurementFamilyRemover();
  const handleRemoveMeasurementFamily = useCallback(async () => {
    try {
      const response = await removeMeasurementFamily(measurementFamilyCode);

      switch (response) {
        case MeasurementFamilyRemoverResult.Success:
          notify(NotificationLevel.SUCCESS, translate('measurements.family.delete.flash.success'));
          history.push('/');
          break;
        case MeasurementFamilyRemoverResult.NotFound:
        case MeasurementFamilyRemoverResult.Unprocessable:
          throw Error(`Error while deleting the measurement family: ${response}`);
      }
    } catch (error) {
      console.error(error);
      notify(NotificationLevel.ERROR, translate('measurements.family.delete.flash.error'));
    }
  }, [measurementFamilyCode, removeMeasurementFamily, history, notify, translate]);

  const handleNewUnit = useCallback(
    (unit: Unit) => {
      if (null === measurementFamily) {
        return;
      }

      setMeasurementFamily(addUnit(measurementFamily, unit));
      selectUnitCode(unit.code);
    },
    [setMeasurementFamily, measurementFamily, selectUnitCode]
  );

  if (undefined === measurementFamilyCode || null === measurementFamily) {
    return null;
  }

  if (undefined === measurementFamily) {
    return (
      <FullScreenError
        title={translate('error.exception', {status_code: '404'})}
        message={translate('measurements.family.not_found')}
        code={404}
      />
    );
  }

  const [unitsErrors, propertiesErrors, otherErrors] = partitionErrors(errors, [
    error => error.propertyPath.startsWith('units'),
    error => error.propertyPath.startsWith('code') || error.propertyPath.startsWith('labels'),
  ]);

  const measurementFamilyLabel = getMeasurementFamilyLabel(measurementFamily, locale);

  return (
    <>
      <Prompt when={isModified} message={() => translate('pim_ui.flash.unsaved_changes')} />
      {isAddUnitModalOpen && (
        <CreateUnit measurementFamily={measurementFamily} onClose={closeAddUnitModal} onNewUnit={handleNewUnit} />
      )}
      {isConfirmDeleteMeasurementFamilyModalOpen && (
        <DeleteModal
          title={translate('measurements.title.measurement')}
          onConfirm={handleRemoveMeasurementFamily}
          onCancel={closeConfirmDeleteMeasurementFamilyModal}
        >
          {translate('measurements.family.delete.confirm')}
        </DeleteModal>
      )}
      <PageHeader showPlaceholder={null === measurementFamily}>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step onClick={() => router.redirect(settingsHref)}>
              {translate('pim_menu.tab.settings')}
            </Breadcrumb.Step>
            <Breadcrumb.Step href={history.createHref({pathname: '/'})}>
              {translate('pim_menu.item.measurements')}
            </Breadcrumb.Step>
            <Breadcrumb.Step>{measurementFamilyLabel}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
            viewName="pim-measurements-user-navigation"
          />
        </PageHeader.UserActions>
        <PageHeader.Actions>
          {isGranted('akeneo_measurements_measurement_family_delete') && !measurementFamily.is_locked && (
            <SecondaryActions>
              <SecondaryActions.Item onClick={openConfirmDeleteMeasurementFamilyModal}>
                {translate('measurements.family.delete.button')}
              </SecondaryActions.Item>
            </SecondaryActions>
          )}
          {isGranted('akeneo_measurements_measurement_unit_add') && (
            <Button
              level="secondary"
              ghost={true}
              onClick={openAddUnitModal}
              disabled={config.units_max <= measurementFamily.units.length}
            >
              {translate('measurements.unit.add')}
            </Button>
          )}
          {(isGranted('akeneo_measurements_measurement_unit_edit') ||
            isGranted('akeneo_measurements_measurement_family_edit_properties')) && (
            <Button onClick={handleSaveMeasurementFamily}>{translate('pim_common.save')}</Button>
          )}
        </PageHeader.Actions>
        <PageHeader.Title>{measurementFamilyLabel ?? measurementFamilyCode}</PageHeader.Title>
        <PageHeader.State>{isModified && <UnsavedChanges />}</PageHeader.State>
      </PageHeader>
      <PageContent>
        <TabsContainer>
          <Tabs>
            {Object.values(Tab).map((tab: Tab) => (
              <TabSelector key={tab} onClick={() => setCurrentTab(tab)} isActive={currentTab === tab}>
                {translate(`measurements.family.tab.${tab}`)}
                {tab === Tab.Units && 0 < unitsErrors.length && <Pill level="danger" />}
                {tab === Tab.Properties && 0 < propertiesErrors.length && <Pill level="danger" />}
              </TabSelector>
            ))}
          </Tabs>
          <Errors errors={[...unitsErrors.filter(error => error.propertyPath === 'units'), ...otherErrors]} />
          {measurementFamily.is_locked && <Helper level="warning">{translate('measurements.family.is_locked')}</Helper>}
        </TabsContainer>
        <Container>
          {currentTab === Tab.Units && null !== selectedUnitCode && (
            <UnitTab
              measurementFamily={measurementFamily}
              onMeasurementFamilyChange={setMeasurementFamily}
              errors={filterErrors(unitsErrors, 'units')}
              selectedUnitCode={selectedUnitCode}
              selectUnitCode={selectUnitCode}
            />
          )}
          {currentTab === Tab.Properties && (
            <PropertyTab
              measurementFamily={measurementFamily}
              onMeasurementFamilyChange={setMeasurementFamily}
              errors={propertiesErrors}
            />
          )}
        </Container>
      </PageContent>
    </>
  );
};

export {Edit};
