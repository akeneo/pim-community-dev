import React from 'react';
import {mockResponse, render, screen, fireEvent} from '../../tests/test-utils';
import {SelectionTab} from '../SelectionTab';
import {CONDITION_NAMES} from '../../models';

jest.mock('../conditions/AddConditionButton');
jest.mock('../conditions/EnabledLine');

describe('SelectionTab', () => {
  it('should render the selection tab', () => {
    render(<SelectionTab target={'sku'} conditions={[]} onChange={jest.fn()} validationErrors={[]} />);

    expect(screen.getByText('pim_identifier_generator.tabs.product_selection')).toBeInTheDocument();
  });

  it('should render the default identifier attribute', async () => {
    const expectCall = mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });

    render(<SelectionTab target={'sku'} conditions={[]} onChange={jest.fn()} validationErrors={[]} />);

    expect(await screen.findByText('Sku')).toBeInTheDocument();

    expectCall();
  });

  it('should render the default identifier attribute with target as attribute', async () => {
    const expectCall = mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [],
    });

    render(<SelectionTab target={'target'} conditions={[]} onChange={jest.fn()} validationErrors={[]} />);

    expect(await screen.findByText('[target]')).toBeInTheDocument();

    expectCall();
  });

  it('should callback on change', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });

    const onChange = jest.fn();
    render(
      <SelectionTab
        target={'sku'}
        conditions={[{type: CONDITION_NAMES.ENABLED, value: true}]}
        onChange={onChange}
        validationErrors={[]}
      />
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
    render(<SelectionTab target={'sku'} conditions={[]} onChange={onChange} validationErrors={[]} />);

    expect(await screen.findByText('Sku')).toBeInTheDocument();
    expect(screen.getByText('AddConditionButtonMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Add condition'));
    expect(screen.getByText('EnabledLineMock')).toBeInTheDocument();
    expect(onChange).toBeCalledWith([{type: CONDITION_NAMES.ENABLED}]);
  });

  it('should show displayed errors', () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });

    const onChange = jest.fn();
    const validationErrors = [{path: 'conditions', message: 'should contain only 1 enabled'}];
    render(<SelectionTab target={'sku'} conditions={[]} onChange={onChange} validationErrors={validationErrors} />);

    expect(screen.getByText('should contain only 1 enabled')).toBeInTheDocument();
  });
});
