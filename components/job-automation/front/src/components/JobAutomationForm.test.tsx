import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders, ValidationError, useFeatureFlags} from '@akeneo-pim-community/shared';
import {JobAutomationForm} from './JobAutomationForm';
import {Automation} from 'model';

const automation: Automation = {
  running_user_groups: ['IT Support'],
};

jest.mock('../hooks/useUserGroups', () => ({
  useUserGroups: () => {
    return [
      'IT Support',
      'Manager',
      'Furniture manager',
      'Clothes manager',
      'Redactor',
      'English translator',
      'SAP Connection',
      'Alkemics Connection',
      'Translations.com Connection',
      'Magento Connection',
    ];
  },
}));

let mockedFeatureFlags = ['permission'];
let mockedGrantedACL = ['pim_user_group_index'];

jest.mock('@akeneo-pim-community/shared/lib/hooks/useFeatureFlags', () => ({
  useFeatureFlags: () => ({
    isEnabled: (featureFlag: string) => {
      return mockedFeatureFlags.includes(featureFlag);
    },
  }),
}));

jest.mock('@akeneo-pim-community/shared/lib/hooks/useSecurity', () => ({
  useSecurity: () => ({
    isGranted: (acl: string) => {
      return mockedGrantedACL.includes(acl);
    },
  }),
}));

beforeEach(() => {
  mockedFeatureFlags = ['permission'];
  mockedGrantedACL = ['pim_user_group_index'];
});

test('it renders the job automation form', () => {
  renderWithProviders(
    <JobAutomationForm automation={automation} validationErrors={[]} onAutomationChange={jest.fn()} />
  );

  expect(screen.getByText('akeneo.job_automation.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo.job_automation.scheduling.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo.job_automation.scheduling.running_user_groups.label')).toBeInTheDocument();
  expect(screen.getByText('IT Support')).toBeInTheDocument();
});

test('it hides the running user group input if the permission is not enabled', () => {
  mockedFeatureFlags = [];

  renderWithProviders(
    <JobAutomationForm automation={automation} validationErrors={[]} onAutomationChange={jest.fn()} />
  );

  expect(screen.queryByText('akeneo.job_automation.scheduling.running_user_groups.label')).not.toBeInTheDocument();
});

test('it disables the running user group input if the user cannot list the user groups', () => {
  mockedGrantedACL = [];

  renderWithProviders(
    <JobAutomationForm automation={automation} validationErrors={[]} onAutomationChange={jest.fn()} />
  );

  expect(screen.getByLabelText('akeneo.job_automation.scheduling.running_user_groups.label')).toBeDisabled();
  expect(screen.getByText('akeneo.job_automation.scheduling.running_user_groups.disabled_helper')).toBeInTheDocument();
});

test('it can change the running user group', () => {
  const onAutomationChange = jest.fn();

  renderWithProviders(
    <JobAutomationForm automation={automation} validationErrors={[]} onAutomationChange={onAutomationChange} />
  );

  userEvent.click(screen.getByLabelText('akeneo.job_automation.scheduling.running_user_groups.label'));
  userEvent.click(screen.getByText('Clothes manager'));
  expect(onAutomationChange).toBeCalledWith({
    running_user_groups: ['IT Support', 'Clothes manager'],
  });
});

test('it displays validation errors', () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.a_type_error',
      invalidValue: '',
      message: 'this is a type error',
      parameters: {},
      propertyPath: '[running_user_groups]',
    },
  ];

  renderWithProviders(
    <JobAutomationForm automation={automation} validationErrors={validationErrors} onAutomationChange={jest.fn()} />
  );

  expect(screen.getByText('error.key.a_type_error')).toBeInTheDocument();
});
