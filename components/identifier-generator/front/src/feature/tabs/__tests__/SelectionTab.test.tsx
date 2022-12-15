import React from 'react';
import {mockResponse, render, screen, fireEvent} from '../../tests/test-utils';
import {SelectionTab} from '../SelectionTab';
import {CONDITION_NAMES} from '../../models';

jest.mock('../conditions/AddConditionButton');
jest.mock('../conditions/EnabledLine');
jest.mock('../../pages/DeletePropertyModal');

describe('SelectionTab', () => {
  it('should render the selection tab', () => {
    render(<SelectionTab target={'sku'} conditions={[]} onChange={jest.fn()} />);

    expect(screen.getByText('pim_identifier_generator.tabs.product_selection')).toBeInTheDocument();
  });

  it('should render the default identifier attribute', async () => {
    const expectCall = mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });

    render(<SelectionTab target={'sku'} conditions={[]} onChange={jest.fn()} />);

    expect(await screen.findByText('Sku')).toBeInTheDocument();

    expectCall();
  });

  it('should callback on change', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });

    const onChange = jest.fn();
    render(
      <SelectionTab target={'sku'} conditions={[{type: CONDITION_NAMES.ENABLED, value: true}]} onChange={onChange} />
    );

    expect(await screen.findByText('Sku')).toBeInTheDocument();

    expect(screen.getByText('EnabledLineMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Update value'));
    expect(onChange).toBeCalledWith([{type: CONDITION_NAMES.ENABLED, value: false}]);
  });

  it('should add a condition', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });

    const onChange = jest.fn();
    render(<SelectionTab target={'sku'} conditions={[]} onChange={onChange} />);

    expect(await screen.findByText('Sku')).toBeInTheDocument();
    expect(screen.getByText('AddConditionButtonMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Add condition'));
    expect(screen.getByText('EnabledLineMock')).toBeInTheDocument();
    expect(onChange).toBeCalledWith([{type: CONDITION_NAMES.ENABLED}]);
  });

  it('should delete a condition', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });

    const onChange = jest.fn();
    render(<SelectionTab target={'sku'} conditions={[{type: CONDITION_NAMES.ENABLED, value: true}]} onChange={onChange} />);

    expect(await screen.findByText('Sku')).toBeInTheDocument();

    expect(screen.getByText('EnabledLineMock')).toBeInTheDocument();
    expect(screen.getByText('Delete Enabled')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Delete Enabled'));
    expect(screen.getByText('DeletePropertyModalMock')).toBeInTheDocument();
  });
});
