import React from 'react';
import styled from 'styled-components';
import {SectionTitle, Field, SelectInput, Helper, MultiSelectInput, BooleanInput} from 'akeneo-design-system';
import {
  Section,
  useTranslate,
  ValidationError,
  filterErrors,
  useFeatureFlags,
  useSecurity,
} from '@akeneo-pim-community/shared';
import {Automation, CronExpression, filterDefaultUserGroup} from '../models';
import {useUserGroups} from '../hooks';
import {CronExpressionForm} from './CronExpressionForm';

const SpacedSection = styled(Section)`
  margin-top: 20px;
`;

type JobAutomationFormProps = {
  scheduled: boolean;
  automation: Automation;
  validationErrors: ValidationError[];
  onScheduledChange: (scheduled: boolean) => void;
  onAutomationChange: (automation: Automation) => void;
};

const JobAutomationForm = ({
  scheduled,
  automation,
  validationErrors,
  onScheduledChange,
  onAutomationChange,
}: JobAutomationFormProps) => {
  const translate = useTranslate();
  const userGroups = useUserGroups();
  const {isEnabled} = useFeatureFlags();
  const {isGranted} = useSecurity();

  const handleScheduledChange = (isEnabled: boolean) => onScheduledChange(isEnabled);

  const handleCronExpressionChange = (cronExpression: CronExpression) =>
    onAutomationChange({
      ...automation,
      cron_expression: cronExpression,
    });

  return (
    <SpacedSection>
      <SectionTitle>
        <SectionTitle.Title>{translate('akeneo.job_automation.title')}</SectionTitle.Title>
      </SectionTitle>
      <Field label={translate('akeneo.job_automation.scheduling.enable')}>
        <BooleanInput
          noLabel={translate('pim_common.no')}
          value={scheduled}
          yesLabel={translate('pim_common.yes')}
          readOnly={false}
          onChange={handleScheduledChange}
        />
      </Field>
      {scheduled && (
        <>
          <SectionTitle>
            <SectionTitle.Title level="secondary">
              {translate('akeneo.job_automation.scheduling.title')}
            </SectionTitle.Title>
          </SectionTitle>
          <CronExpressionForm
            cronExpression={automation.cron_expression}
            onCronExpressionChange={handleCronExpressionChange}
            validationErrors={filterErrors(validationErrors, '[cron_expression]')}
          />
          {isEnabled('permission') && (
            <Field label={translate('akeneo.job_automation.scheduling.running_user_groups.label')}>
              <MultiSelectInput
                value={filterDefaultUserGroup(automation.running_user_groups)}
                onChange={runningUserGroups =>
                  onAutomationChange({
                    ...automation,
                    running_user_groups: runningUserGroups,
                  })
                }
                emptyResultLabel={translate('pim_common.no_result')}
                openLabel={translate('pim_common.open')}
                removeLabel={translate('pim_common.remove')}
                readOnly={!isGranted('pim_user_group_index')}
              >
                {filterDefaultUserGroup(userGroups).map(group => (
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
        </>
      )}
    </SpacedSection>
  );
};

export type {JobAutomationFormProps};
export {JobAutomationForm};
