import React from 'react';
import { CreateRulesForm } from '../../../../../../src/pages/CreateRules/components/CreateRulesForm';
import userEvent from '@testing-library/user-event';
import { render, screen, act, fireEvent } from '../../../../../../test-utils';

describe('CreateRulesForm', () => {
  test('should display the form', async () => {
    // Given
    const translate = jest.fn((key: string) => key);
    const onSubmit = jest.fn();
    // When
    render(
      <CreateRulesForm
        translate={translate}
        onSubmit={onSubmit}
        locale='en_US'
      />,
      {
        all: true,
      }
    );
    // Then
    expect(
      await screen.findByText('pimee_catalog_rule.form.creation.title')
    ).toBeInTheDocument();
    expect(
      screen.getByText('pim_common.code pim_common.required_label')
    ).toBeInTheDocument();
    expect(screen.getByText('pim_common.label')).toBeInTheDocument();
    expect(
      screen.getByText('pimee_catalog_rule.form.creation.english_flag')
    ).toBeInTheDocument();
    expect(screen.getByText('pim_common.save')).toBeInTheDocument();
  });
  test('should submit the form with code and label input values', async () => {
    // Given
    const translate = jest.fn((key: string) => key);
    const onSubmit = jest.fn(() => Promise.resolve({ ok: true }));

    // When
    render(
      <CreateRulesForm
        translate={translate}
        onSubmit={onSubmit}
        locale='en_US'
      />,
      {
        all: true,
      }
    );
    const inputCode = (await screen.findByLabelText(
      'pim_common.code pim_common.required_label'
    )) as HTMLInputElement;
    const inputLabel = (await screen.findByLabelText(
      'pim_common.label'
    )) as HTMLInputElement;
    const submitButton = await screen.findByText('pim_common.save');
    expect(submitButton).toBeDisabled();
    await act(async () => {
      await userEvent.type(inputCode, 'my_code');
      await userEvent.type(inputLabel, 'my_label');
      fireEvent.submit(screen.getByTestId('form-create-rules'));
    });
    // Then
    expect(inputCode.value).toBe('my_code');
    expect(inputLabel.value).toBe('my_label');
    expect(submitButton).not.toBeDisabled();
    expect(onSubmit).toHaveBeenNthCalledWith(1, {
      code: 'my_code',
      label: 'my_label',
    });
  });
  test('should keep the submit button disabled if code input is less than 3 characters', async () => {
    // Given
    const translate = jest.fn((key: string) => key);
    const onSubmit = jest.fn();
    // When
    render(
      <CreateRulesForm
        translate={translate}
        onSubmit={onSubmit}
        locale='en_US'
      />,
      {
        all: true,
      }
    );
    const inputCode = (await screen.findByLabelText(
      'pim_common.code pim_common.required_label'
    )) as HTMLInputElement;
    const submitButton = await screen.findByText('pim_common.save');
    expect(submitButton).toBeDisabled();
    await act(async () => {
      await userEvent.type(inputCode, '__');
    });
    // Then
    expect(inputCode.value).toBe('__');
    expect(submitButton).toBeDisabled();
    expect(
      screen.getByText(
        'pimee_catalog_rule.form.creation.constraint.code.too_short'
      )
    ).toBeInTheDocument();
  });
  test('should keep the submit button disabled if code input contains characters others than alphanumeric and underscore', async () => {
    // Given
    const translate = jest.fn((key: string) => key);
    const onSubmit = jest.fn();
    // When
    render(
      <CreateRulesForm
        translate={translate}
        onSubmit={onSubmit}
        locale='en_US'
      />,
      {
        all: true,
      }
    );
    const inputCode = (await screen.findByLabelText(
      'pim_common.code pim_common.required_label'
    )) as HTMLInputElement;
    const submitButton = await screen.findByText('pim_common.save');
    expect(submitButton).toBeDisabled();
    await act(async () => {
      await userEvent.type(inputCode, '$$$');
    });
    // Then
    expect(inputCode.value).toBe('$$$');
    expect(submitButton).toBeDisabled();
    expect(
      screen.getByText(
        'pimee_catalog_rule.form.creation.constraint.code.allowed_characters'
      )
    ).toBeInTheDocument();
  });
  // test('should keep the submit button disabled if code input is empty (after change)', async () => {
  //   // Given
  //   const translate = jest.fn((key: string) => key);
  //   const onSubmit = jest.fn();
  //   // When
  //   render(
  //     <CreateRulesForm
  //       translate={translate}
  //       onSubmit={onSubmit}
  //       locale='en_US'
  //     />,
  //     {
  //     theme: true,
  //     }
  //   );
  //   const submitButton = await screen.findByText('pim_common.save');
  //   expect(submitButton).toBeDisabled();
  //   fireEvent.submit(screen.getByTestId('form-create-rules'));
  //   // });
  //   //Then
  //   expect(submitButton).toBeDisabled();
  //   expect(
  //     screen.getByText(
  //       'pimee_catalog_rule.form.creation.constraint.code.required'
  //     )
  //   ).toBeInTheDocument();
  // });
  test('should display an error return by the submit promise', async () => {
    // Given
    const translate = jest.fn((key: string) => key);
    const onSubmit = jest.fn(() =>
      Promise.resolve({
        ok: false,
        json: jest.fn(() => [
          {
            path: 'code',
            message: 'Error return by promise',
          },
        ]),
      })
    );
    // When
    render(
      <CreateRulesForm
        translate={translate}
        onSubmit={onSubmit}
        locale='en_US'
      />,
      {
        all: true,
      }
    );
    const inputCode = (await screen.findByLabelText(
      'pim_common.code pim_common.required_label'
    )) as HTMLInputElement;

    const submitButton = await screen.findByText('pim_common.save');
    expect(submitButton).toBeDisabled();
    await act(async () => {
      await userEvent.type(inputCode, 'my_code');
      fireEvent.submit(screen.getByTestId('form-create-rules'));
    });
    //Then
    expect(submitButton).not.toBeDisabled();
    expect(screen.getByText('Error return by promise')).toBeInTheDocument();
  });
});
