import React, {useCallback, useContext, useEffect, useState} from 'react';
import {useHistory, useParams, Prompt} from 'react-router-dom';
import styled from 'styled-components';
import {useMeasurementFamily} from '../../hooks/use-measurement-family';
import {UnitTab} from '../../pages/edit/unit-tab';
import {PropertyTab} from '../../pages/edit/PropertyTab';
import {PageHeader, PageHeaderPlaceholder} from '../../shared/components/PageHeader';
import {addUnit, getMeasurementFamilyLabel, MeasurementFamily} from '../../model/measurement-family';
import {Unit, UnitCode} from '../../model/unit';
import {PageContent} from '../../shared/components/PageContent';
import {DropdownLink, SecondaryActionsDropdownButton} from '../../shared/components/SecondaryActionsDropdownButton';
import {useSaveMeasurementFamilySaver} from '../../pages/edit/hooks/use-save-measurement-family-saver';
import {ErrorBadge} from '../../shared/components/ErrorBadge';
import {CreateUnit} from '../../pages/create-unit/CreateUnit';
import {useUnsavedChanges} from '../../shared/hooks/use-unsaved-changes';
import {UnsavedChanges} from '../../shared/components/UnsavedChanges';
import {UnsavedChangesContext} from '../../context/unsaved-changes-context';
import {useMeasurementFamilyRemover, MeasurementFamilyRemoverResult} from '../../hooks/use-measurement-family-remover';
import {ConfirmDeleteModal} from '../../shared/components/ConfirmDeleteModal';
import {ConfigContext} from '../../context/config-context';
import {ErrorBlock} from '../../shared/components/ErrorBlock';
import {
  useTranslate,
  useNotify,
  NotificationLevel,
  useUserContext,
  useSecurity,
  useRoute,
} from '@akeneo-pim-community/legacy';
import {filterErrors, ValidationError, partitionErrors, useToggleState} from '@akeneo-pim-community/shared';
import {Helper, Button, Breadcrumb} from 'akeneo-design-system';

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
  const __ = useTranslate();
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
  const [isAddUnitModalOpen, openAddUnitModal, closeAddUnitModal] = useToggleState(false);
  const [
    isConfirmDeleteMeasurementFamilyModalOpen,
    openConfirmDeleteMeasurementFamilyModal,
    closeConfirmDeleteMeasurementFamilyModal,
  ] = useToggleState(false);
  const settingsHref = `#${useRoute('pim_enrich_attribute_index')}`;

  const {setHasUnsavedChanges} = useContext(UnsavedChangesContext);
  const [isModified, resetState] = useUnsavedChanges<MeasurementFamily | null>(
    measurementFamily,
    __('pim_ui.flash.unsaved_changes')
  );
  useEffect(() => setHasUnsavedChanges(isModified), [isModified, setHasUnsavedChanges]);

  // If the measurement family code changes, we select the standard unit code by default
  useEffect(() => {
    if (null === measurementFamily) {
      return;
    }

    selectUnitCode(measurementFamily.standard_unit_code);
  }, [measurementFamily]);

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
  }, [measurementFamily, saveMeasurementFamily, notify, __, setErrors, resetState]);

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
      selectUnitCode(unit.code);
    },
    [setMeasurementFamily, measurementFamily, selectUnitCode]
  );

  if (undefined === measurementFamilyCode || null === measurementFamily) {
    return null;
  }

  if (undefined === measurementFamily) {
    return (
      <ErrorBlock
        title={__('error.exception', {status_code: '404'})}
        message={__('measurements.family.not_found')}
        code={404}
      />
    );
  }

  const buttons = [];
  if (isGranted('akeneo_measurements_measurement_family_delete') && !measurementFamily.is_locked) {
    buttons.push(
      <SecondaryActionsDropdownButton title={__('pim_common.other_actions')} key={0}>
        <DropdownLink onClick={openConfirmDeleteMeasurementFamilyModal}>
          {__('measurements.family.delete.button')}
        </DropdownLink>
      </SecondaryActionsDropdownButton>
    );
  }

  if (isGranted('akeneo_measurements_measurement_unit_add')) {
    buttons.push(
      <Button
        level="secondary"
        ghost={true}
        onClick={openAddUnitModal}
        disabled={config.units_max <= measurementFamily.units.length}
      >
        {__('measurements.unit.add')}
      </Button>
    );
  }

  if (
    isGranted('akeneo_measurements_measurement_unit_edit') ||
    isGranted('akeneo_measurements_measurement_family_edit_properties')
  ) {
    buttons.push(<Button onClick={handleSaveMeasurementFamily}>{__('pim_common.save')}</Button>);
  }

  const [unitsErrors, propertiesErrors, otherErrors] = partitionErrors(errors, [
    error => error.propertyPath.startsWith('units'),
    error => error.propertyPath.startsWith('code') || error.propertyPath.startsWith('labels'),
  ]);

  const measurementFamilyLabel = getMeasurementFamilyLabel(measurementFamily, locale);

  return (
    <>
      <Prompt when={isModified} message={() => __('pim_ui.flash.unsaved_changes')} />
      <CreateUnit
        isOpen={isAddUnitModalOpen}
        measurementFamily={measurementFamily}
        onClose={closeAddUnitModal}
        onNewUnit={handleNewUnit}
      />
      <ConfirmDeleteModal
        isOpen={isConfirmDeleteMeasurementFamilyModalOpen}
        description={__('measurements.family.delete.confirm')}
        onConfirm={handleRemoveMeasurementFamily}
        onCancel={closeConfirmDeleteMeasurementFamilyModal}
      />
      <PageHeader
        userButtons={undefined}
        buttons={buttons}
        breadcrumb={
          <Breadcrumb>
            <Breadcrumb.Step href={settingsHref}>{__('pim_menu.tab.settings')}</Breadcrumb.Step>
            <Breadcrumb.Step href={history.createHref({pathname: '/'})}>
              {__('pim_menu.item.measurements')}
            </Breadcrumb.Step>
            <Breadcrumb.Step>{measurementFamilyLabel}</Breadcrumb.Step>
          </Breadcrumb>
        }
        state={isModified && <UnsavedChanges />}
      >
        {null === measurementFamily ? (
          <div className={`AknLoadingPlaceHolderContainer`}>
            <PageHeaderPlaceholder />
          </div>
        ) : (
          <div>{measurementFamilyLabel}</div>
        )}
      </PageHeader>

      <PageContent>
        <TabsContainer>
          <Tabs>
            {Object.values(Tab).map((tab: Tab) => (
              <TabSelector key={tab} onClick={() => setCurrentTab(tab)} isActive={currentTab === tab}>
                {__(`measurements.family.tab.${tab}`)}
                {tab === Tab.Units && 0 < unitsErrors.length && <ErrorBadge />}
                {tab === Tab.Properties && 0 < propertiesErrors.length && <ErrorBadge />}
              </TabSelector>
            ))}
          </Tabs>
          <Errors errors={[...unitsErrors.filter(error => error.propertyPath === 'units'), ...otherErrors]} />
          {measurementFamily.is_locked && <Helper level="warning">{__('measurements.family.is_locked')}</Helper>}
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
