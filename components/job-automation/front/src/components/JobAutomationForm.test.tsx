import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {JobAutomationForm} from './JobAutomationForm';
import {Automation} from '../models';

const automation: Automation = {
  cron_expression: '0 0 * * *',
  running_user_groups: ['IT Support'],
  notification_user_groups: ['Manager'],
  notification_users: ['admin'],
};

const intersectionObserverMock = () => ({
    observe: () => null,
    unobserve: () => null
})
window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

jest.mock('../hooks/useUserGroups', () => ({
  useUserGroups: () => {
      return {
          availableUserGroups: [
              {id: 1, label: 'IT Support'},
              {id: 2, label: 'Manager'},
              {id: 3, label: 'Furniture manager'},
              {id: 4, label: 'Clothes manager'},
              {id: 5, label: 'Redactor'},
              {id: 6, label: 'English translator'},
              {id: 7, label: 'SAP Connection'},
              {id: 8, label: 'Alkemics Connection'},
              {id: 9, label: 'Translations.com Connection'},
              {id: 10, label: 'Magento Connection'},
          ],
          loadNextPage: () => [],
      }
  }
}));

jest.mock('../hooks/useUsers', () => ({
    useUsers: () => {
        return {
            availableUsers: [
                {id: 1, username: 'admin'},
                {id: 2, username: 'julia'},
                {id: 3, username: 'julien'},
                {id: 4, username: 'mary'},
                {id: 5, username: 'pamela'},
                {id: 6, username: 'peter'},
                {id: 7, username: 'sandra'},
            ],
            loadNextPage: () => []
        }
    }
}));

let mockedFeatureFlags = ['permission'];
let mockedGrantedACL = ['pim_user_group_index', 'pim_user_user_index'];

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
  mockedGrantedACL = ['pim_user_group_index', 'pim_user_user_index'];
});

test('it renders the job automation form', () => {
  renderWithProviders(
    <JobAutomationForm
      scheduled={true}
      automation={automation}
      validationErrors={[]}
      onScheduledChange={jest.fn()}
      onAutomationChange={jest.fn()}
    />
  );

  expect(screen.getByText('akeneo.job_automation.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo.job_automation.scheduling.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo.job_automation.scheduling.running_user_groups.label')).toBeInTheDocument();
  expect(screen.getByText('akeneo.job_automation.notification.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo.job_automation.notification.user_groups.label')).toBeInTheDocument();
  expect(screen.getByText('akeneo.job_automation.notification.users.label')).toBeInTheDocument();
  expect(screen.getByText('IT Support')).toBeInTheDocument();
});


test('it hides the running user group input if the permission is not enabled', () => {
  mockedFeatureFlags = [];

  renderWithProviders(
    <JobAutomationForm
      scheduled={true}
      automation={automation}
      validationErrors={[]}
      onScheduledChange={jest.fn()}
      onAutomationChange={jest.fn()}
    />
  );

  expect(screen.queryByText('akeneo.job_automation.scheduling.running_user_groups.label')).not.toBeInTheDocument();
});

test('it disables the running user group input if the user cannot list the user groups', () => {
  mockedGrantedACL = [];

  renderWithProviders(
    <JobAutomationForm
      scheduled={true}
      automation={automation}
      validationErrors={[]}
      onScheduledChange={jest.fn()}
      onAutomationChange={jest.fn()}
    />
  );

  expect(screen.getByLabelText('akeneo.job_automation.scheduling.running_user_groups.label')).toBeDisabled();
  expect(screen.getByText('akeneo.job_automation.scheduling.running_user_groups.disabled_helper')).toBeInTheDocument();
});

test('it can change the running user groups', () => {
  const onAutomationChange = jest.fn();

  renderWithProviders(
    <JobAutomationForm
      scheduled={true}
      automation={automation}
      validationErrors={[]}
      onScheduledChange={jest.fn()}
      onAutomationChange={onAutomationChange}
    />
  );

  userEvent.click(screen.getByLabelText('akeneo.job_automation.scheduling.running_user_groups.label'));
  userEvent.click(screen.getByText('Clothes manager'));
  expect(onAutomationChange).toBeCalledWith({
    ...automation,
    running_user_groups: ['IT Support', 'Clothes manager'],
  });
});

test('it can change the notification user groups', () => {
  const onAutomationChange = jest.fn();

  renderWithProviders(
    <JobAutomationForm
      scheduled={true}
      onScheduledChange={jest.fn()}
      automation={automation}
      validationErrors={[]}
      onAutomationChange={onAutomationChange}
    />
  );

  userEvent.click(screen.getByLabelText('akeneo.job_automation.notification.user_groups.label'));
  userEvent.click(screen.getByText('Clothes manager'));
  expect(onAutomationChange).toBeCalledWith({
    ...automation,
    notification_user_groups: ['Manager', 'Clothes manager'],
  });
});

test('it can change the notification users', () => {
  const onAutomationChange = jest.fn();

  renderWithProviders(
    <JobAutomationForm
      scheduled={true}
      onScheduledChange={jest.fn()}
      automation={automation}
      validationErrors={[]}
      onAutomationChange={onAutomationChange}
    />
  );

  userEvent.click(screen.getByLabelText('akeneo.job_automation.notification.users.label'));
  userEvent.click(screen.getByText('julia'));
  expect(onAutomationChange).toBeCalledWith({
    ...automation,
    notification_users: ['admin', 'julia'],
  });
});

test('it can disable the scheduling', () => {
  const onScheduledChange = jest.fn();

  renderWithProviders(
    <JobAutomationForm
      scheduled={true}
      automation={automation}
      validationErrors={[]}
      onScheduledChange={onScheduledChange}
      onAutomationChange={jest.fn()}
    />
  );

  userEvent.click(screen.getByText('pim_common.no'));

  expect(onScheduledChange).toBeCalledWith(false);
});

test('it can update the scheduling', () => {
  const onAutomationChange = jest.fn();

  renderWithProviders(
    <JobAutomationForm
      scheduled={true}
      automation={automation}
      validationErrors={[]}
      onScheduledChange={jest.fn()}
      onAutomationChange={onAutomationChange}
    />
  );

  const [openFrequencyOptionButton] = screen.getAllByTitle('pim_common.open');

  userEvent.click(openFrequencyOptionButton);
  userEvent.click(screen.getByText('akeneo.job_automation.scheduling.frequency.weekly'));

  expect(onAutomationChange).toBeCalledWith({
    ...automation,
    cron_expression: '0 0 * * 0',
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
    <JobAutomationForm
      scheduled={true}
      automation={automation}
      validationErrors={validationErrors}
      onScheduledChange={jest.fn()}
      onAutomationChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.a_type_error')).toBeInTheDocument();
});

