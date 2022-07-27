import React from 'react';
import {SectionTitle, Field, SelectInput, Helper, MultiSelectInput} from 'akeneo-design-system';
import {
  Section,
  useTranslate,
  ValidationError,
  filterErrors,
  useFeatureFlags,
  useSecurity,
} from '@akeneo-pim-community/shared';
import {Automation} from '../model';
import {useUserGroups} from '../hooks';

type JobAutomationFormProps = {
  automation: Automation;
  validationErrors: ValidationError[];
  onAutomationChange: (automation: Automation) => void;
};

const JobAutomationForm = ({automation, validationErrors, onAutomationChange}: JobAutomationFormProps) => {
  const translate = useTranslate();
  const userGroups = useUserGroups();
  const featureFlags = useFeatureFlags();
  const {isGranted} = useSecurity();

  return (
    <Section>
      <SectionTitle>
        <SectionTitle.Title>{translate('akeneo.job_automation.title')}</SectionTitle.Title>
      </SectionTitle>
      <SectionTitle>
        <SectionTitle.Title level="secondary">{translate('akeneo.job_automation.scheduling.title')}</SectionTitle.Title>
      </SectionTitle>
      {featureFlags.isEnabled('permission') && (
        <Field label={translate('akeneo.job_automation.scheduling.running_user_groups.label')}>
          <MultiSelectInput
            value={automation.running_user_groups}
            onChange={runningUserGroups => onAutomationChange({...automation, running_user_groups: runningUserGroups})}
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            removeLabel={translate('pim_common.remove')}
            readOnly={!isGranted('pim_user_group_index')}
          >
            {userGroups.map(group => (
              <SelectInput.Option value={group} key={group}>
                {group}
              </SelectInput.Option>
            ))}
          </MultiSelectInput>
          {filterErrors(validationErrors, '[running_user_groups]').map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
          {!isGranted('pim_user_group_index') && (
            <Helper level="info">
              {translate('akeneo.job_automation.scheduling.running_user_groups.disabled_helper')}
            </Helper>
          )}
        </Field>
      )}
    </Section>
  );
};

export type {JobAutomationFormProps};

export {JobAutomationForm};
