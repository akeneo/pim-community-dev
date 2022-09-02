import React from 'react';
import styled from 'styled-components';
import {filterErrors, Section, ValidationError} from '@akeneo-pim-community/shared';
import {ErrorActionInput} from './components';
import {ErrorAction} from './models';

const GlobalSettingTabContainer = styled.div`
  margin: 20px 0 10px;
`;

type GlobalSettings = {
  error_action: ErrorAction;
};

type GlobalSettingsTabProps = {
  globalSettings: GlobalSettings;
  validationErrors: ValidationError[];
  onGlobalSettingsChange: (newGlobalSettings: GlobalSettings) => void;
};

const GlobalSettingsTab = ({globalSettings, onGlobalSettingsChange, validationErrors}: GlobalSettingsTabProps) => {
  const handleErrorActionChanged = (errorAction: ErrorAction) => {
    onGlobalSettingsChange({
      ...globalSettings,
      error_action: errorAction,
    });
  };

  return (
    <GlobalSettingTabContainer>
      <Section>
        <ErrorActionInput
          value={globalSettings.error_action}
          onChange={handleErrorActionChanged}
          validationErrors={filterErrors(validationErrors, '[error_action]')}
        />
      </Section>
    </GlobalSettingTabContainer>
  );
};

export {GlobalSettingsTab};
export type {GlobalSettingsTabProps, GlobalSettings};
