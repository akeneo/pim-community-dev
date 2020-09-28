import React from 'react';
import 'jest-fetch-mock';
import { CreateRules } from '../../../../src/pages/CreateRules';
import userEvent from '@testing-library/user-event';
import { render, screen, act, fireEvent } from '../../../../test-utils';

describe('CreateRules', () => {
  it('should render the page', async () => {
    // Given nothing
    // When
    render(<CreateRules />, { legacy: true });
    // Then
    expect(await screen.findByText('pim_menu.item.rule /')).toBeInTheDocument();
    expect(
      screen.getByText('pimee_catalog_rule.form.creation.helper')
    ).toBeInTheDocument();
    expect(
      screen.getByText(
        'pimee_catalog_rule.form.helper.product_selection_doc_link'
      )
    ).toBeInTheDocument();
    expect(
      screen.getByText('pimee_catalog_rule.form.creation.cross_link')
    ).toBeInTheDocument();
  });
  it('should redirect to the rules list', async () => {
    // Given nothing
    // When
    render(<CreateRules />, { legacy: true });
    const leaveButton = (await screen.findByTestId(
      'leave-page-button'
    )) as HTMLAnchorElement;
    // Then
    expect(leaveButton).toHaveAttribute(
      'href',
      '#pimee_catalog_rule_rule_index'
    );
  });
  it('should make a resolve http post on rule endpoint', async () => {
    // Given
    fetchMock.once(async () => {
      return new Promise(resolve =>
        setTimeout(() => resolve({ body: 'ok' }), 100)
      );
    });
    // When
    render(<CreateRules />, { legacy: true });
    const inputCode = (await screen.findByLabelText(
      'pim_common.code pim_common.required_label'
    )) as HTMLInputElement;
    const submitButton = await screen.findByText('pim_common.save');
    expect(submitButton).toBeDisabled();
    // Then
    await act(async () => {
      await userEvent.type(inputCode, 'my_code');
    });
    fireEvent.submit(screen.getByTestId('form-create-rules'));
    // Then
    expect(await screen.findByTestId('akeneo-spinner')).toBeInTheDocument();
  });
  it('should render the duplicate page', async () => {
    // When
    render(<CreateRules originalRuleCode={'my_already_existing_rule'} />, {
      legacy: true,
    });
    // Then
    expect(await screen.findByText('pim_menu.item.rule /')).toBeInTheDocument();
    expect(
      screen.getByText('pimee_catalog_rule.form.edit.duplicate.title')
    ).toBeInTheDocument();
    expect(
      screen.getByText('pimee_catalog_rule.form.creation.helper')
    ).toBeInTheDocument();
    expect(
      screen.getByText(
        'pimee_catalog_rule.form.helper.product_selection_doc_link'
      )
    ).toBeInTheDocument();
    expect(
      screen.getByText('pimee_catalog_rule.form.creation.cross_link')
    ).toBeInTheDocument();
  });
  it('should redirect to the original rule edit page', async () => {
    // When
    render(<CreateRules originalRuleCode={'original_rule'} />, {
      legacy: true,
    });
    const leaveButton = (await screen.findByTestId(
      'leave-page-button'
    )) as HTMLAnchorElement;
    // Then
    expect(leaveButton).toHaveAttribute('href');
    expect(leaveButton.getAttribute('href')).toMatch(
      /^#pimee_catalog_rule_edit/
    );
  });
  it('should submit the data to the duplicate rule endpoint', async () => {
    // Given
    fetchMock.once(async request => {
      if (!request.url.includes('pimee_enrich_rule_definition_duplicate')) {
        throw new Error(`The ${request.url} route was not expected`);
      }
      return new Promise(resolve =>
        setTimeout(() => resolve({ body: 'ok' }), 100)
      );
    });
    // When
    render(<CreateRules originalRuleCode={'original_rule'} />, {
      legacy: true,
    });
    const inputCode = (await screen.findByLabelText(
      'pim_common.code pim_common.required_label'
    )) as HTMLInputElement;
    const submitButton = await screen.findByText('pim_common.save');
    expect(submitButton).toBeDisabled();
    await act(async () => {
      await userEvent.type(inputCode, 'my_code');
    });
    fireEvent.submit(screen.getByTestId('form-create-rules'));
    // Then
    expect(await screen.findByTestId('akeneo-spinner')).toBeInTheDocument();
  });
});
