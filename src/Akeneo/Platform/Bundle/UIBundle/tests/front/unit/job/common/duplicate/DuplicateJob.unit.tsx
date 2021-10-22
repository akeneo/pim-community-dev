import React from 'react';
import {act, screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {DuplicateJob} from '../../../../../../Resources/public/js/job/common/duplicate/DuplicateJob';
import userEvent from '@testing-library/user-event';
import {dependencies} from '../../../../../../Resources/workspaces/legacy-bridge';

test('It renders a modal when user duplicate a job', () => {
  renderWithProviders(
    <DuplicateJob subTitle="Exports" jobCodeToDuplicate="my_job_to_duplicate" successRedirectRoute="url_to_redirect" />
  );

  act(() => {
    userEvent.click(screen.getByText('pim_common.duplicate'));
  });

  expect(screen.getByText('Exports')).toBeInTheDocument();
  expect(screen.getByText('pim_import_export.entity.job_instance.duplicate.title')).toBeInTheDocument();
  expect(screen.getByLabelText('pim_common.label pim_common.required_label')).toBeInTheDocument();
  expect(screen.getByLabelText('pim_common.code pim_common.required_label')).toBeInTheDocument();

  expect(screen.getByText('pim_common.save')).toBeDisabled();
});

test('It duplicates a job', async () => {
  global.fetch = jest.fn().mockImplementation(async (url: string) => {
    switch (url) {
      case 'pim_enrich_job_instance_rest_duplicate':
        return {ok: true, json: () => ({code: 'duplicated_job'})};
      default:
        return {ok: true};
    }
  });

  renderWithProviders(
    <DuplicateJob subTitle="Exports" jobCodeToDuplicate="my_job_to_duplicate" successRedirectRoute="url_to_redirect" />
  );

  act(() => {
    userEvent.click(screen.getByText('pim_common.duplicate'));
  });

  userEvent.type(screen.getByLabelText('pim_common.label pim_common.required_label'), 'duplicated job');

  await act(async () => {
    userEvent.click(screen.getByText('pim_common.save'));
  });

  expect(dependencies.router.redirect).toHaveBeenCalledWith('url_to_redirect');
  expect(dependencies.notify).toHaveBeenCalledWith(
    'success',
    'pim_import_export.entity.job_instance.duplicate.success'
  );
  expect(screen.queryByLabelText('pim_common.label pim_common.required_label')).not.toBeInTheDocument();
  expect(screen.queryByLabelText('pim_common.code pim_common.required_label')).not.toBeInTheDocument();
});

test('It automatically sanitize job code', async () => {
  renderWithProviders(
    <DuplicateJob subTitle="Exports" jobCodeToDuplicate="my_job_to_duplicate" successRedirectRoute="url_to_redirect" />
  );

  act(() => {
    userEvent.click(screen.getByText('pim_common.duplicate'));
  });

  const labelInput = screen.getByLabelText('pim_common.label pim_common.required_label');
  const codeInput = screen.getByLabelText('pim_common.code pim_common.required_label');

  userEvent.clear(labelInput);
  userEvent.type(labelInput, 'duplicated job');
  expect(codeInput).toHaveValue('duplicatedjob');

  userEvent.clear(codeInput);
  userEvent.type(codeInput, 'duplicated_job');
  expect(codeInput).toHaveValue('duplicated_job');

  userEvent.clear(labelInput);
  userEvent.type(labelInput, 'duplicate job');
  expect(codeInput).toHaveValue('duplicated_job');
  expect(labelInput).toHaveValue('duplicate job');
});

test('It clears the code and the label when cancel a duplication', async () => {
  renderWithProviders(
    <DuplicateJob subTitle="Exports" jobCodeToDuplicate="my_job_to_duplicate" successRedirectRoute="url_to_redirect" />
  );

  act(() => {
    userEvent.click(screen.getByText('pim_common.duplicate'));
  });

  userEvent.type(screen.getByLabelText('pim_common.label pim_common.required_label'), 'duplicated job');
  userEvent.click(screen.getByText('pim_common.cancel'));
  act(() => {
    userEvent.click(screen.getByText('pim_common.duplicate'));
  });

  expect(screen.getByLabelText('pim_common.label pim_common.required_label')).toHaveValue('');
  expect(screen.getByLabelText('pim_common.code pim_common.required_label')).toHaveValue('');
});

test('It displays validation errors', async () => {
  global.fetch = jest.fn().mockImplementation(async (url: string) => {
    switch (url) {
      case 'pim_enrich_job_instance_rest_duplicate':
        return {
          ok: false,
          json: () => ({
            values: [
              {
                messageTemplate: 'error.key.label',
                invalidValue: '',
                message: 'this is a label error',
                parameters: {},
                propertyPath: 'label',
              },
              {
                messageTemplate: 'error.key.code',
                invalidValue: '',
                message: 'this is a code error',
                parameters: {},
                propertyPath: 'code',
              },
            ],
          }),
        };
      default:
        return {ok: true};
    }
  });

  renderWithProviders(
    <DuplicateJob subTitle="Exports" jobCodeToDuplicate="my_job_to_duplicate" successRedirectRoute="url_to_redirect" />
  );

  act(() => {
    userEvent.click(screen.getByText('pim_common.duplicate'));
  });

  userEvent.type(screen.getByLabelText('pim_common.label pim_common.required_label'), 'duplicated job');

  await act(async () => {
    userEvent.click(screen.getByText('pim_common.save'));
  });

  //expect(dependencies.router.redirect).not.toHaveBeenCalledWith('url_to_redirect');
  expect(dependencies.notify).toHaveBeenCalledWith('error', 'pim_import_export.entity.job_instance.duplicate.fail');
  act(() => {
    expect(screen.getByText('error.key.code')).toBeInTheDocument();
    expect(screen.getByText('error.key.label')).toBeInTheDocument();
  });
});
