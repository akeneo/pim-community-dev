import React from 'react';
import {fireEvent, mockResponse, render, screen} from '../../tests/test-utils';
import {SelectionTab} from '../SelectionTab';
import {CONDITION_NAMES, Conditions, Operator} from '../../models';

jest.mock('../conditions/AddConditionButton');
jest.mock('../conditions/EnabledLine');
jest.mock('../../pages/SimpleDeleteModal');

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
        conditions={[
          {type: CONDITION_NAMES.ENABLED, value: true},
          {type: CONDITION_NAMES.FAMILY, operator: Operator.EMPTY},
        ]}
        onChange={onChange}
        validationErrors={[]}
      />
    );

    expect(await screen.findByText('Sku')).toBeInTheDocument();

    expect(screen.getByText('EnabledLineMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Update value'));
    expect(onChange).toBeCalledWith([
      {type: CONDITION_NAMES.ENABLED, value: false},
      {type: CONDITION_NAMES.FAMILY, operator: Operator.EMPTY},
    ]);
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

  it('should display a placeholder if conditions are empty', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });
    const onChange = jest.fn();

    render(<SelectionTab target={'sku'} conditions={[]} onChange={onChange} />);
    expect(await screen.findByText('Sku')).toBeInTheDocument();
    expect(screen.queryByText('pim_identifier_generator.selection.empty.title')).toBeInTheDocument();
  });

  it('should not display placeholder there is at least 1 condition', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });
    const onChange = jest.fn();
    render(
      <SelectionTab target={'sku'} conditions={[{type: CONDITION_NAMES.ENABLED, value: true}]} onChange={onChange} />
    );
    expect(await screen.findByText('Sku')).toBeInTheDocument();
    expect(screen.queryByText('pim_identifier_generator.selection.empty.title')).not.toBeInTheDocument();
  });

  it('should delete a condition', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });

    const onChange = jest.fn();
    render(
      <SelectionTab target={'sku'} conditions={[{type: CONDITION_NAMES.ENABLED, value: true}]} onChange={onChange} />
    );

    expect(await screen.findByText('Sku')).toBeInTheDocument();

    expect(screen.getByText('EnabledLineMock')).toBeInTheDocument();
    expect(screen.getByText('Delete Enabled')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Delete Enabled'));
    expect(screen.getByText('SimpleDeleteModalMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Delete property'));
    expect(screen.queryByText('SimpleDeleteModalMock')).not.toBeInTheDocument();
    expect(onChange).toBeCalledWith([]);
  });

  it('should cancel deletion of a condition', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });

    const onChange = jest.fn();
    render(
      <SelectionTab target={'sku'} conditions={[{type: CONDITION_NAMES.ENABLED, value: true}]} onChange={onChange} />
    );

    expect(await screen.findByText('Sku')).toBeInTheDocument();

    expect(screen.getByText('EnabledLineMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Delete Enabled'));
    expect(screen.getByText('SimpleDeleteModalMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Close modal'));
    expect(screen.queryByText('SimpleDeleteModalMock')).not.toBeInTheDocument();
    expect(onChange).not.toBeCalledWith([]);
  });

  it('reorder conditions', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });
    const onChange = jest.fn();
    const conditions: Conditions = [
      {type: CONDITION_NAMES.ENABLED, value: true},
      {type: CONDITION_NAMES.ENABLED, value: false},
      {type: CONDITION_NAMES.ENABLED, value: true},
      {type: CONDITION_NAMES.ENABLED, value: false},
    ];
    render(<SelectionTab target={'sku'} conditions={conditions} onChange={onChange} />);

    let dataTransferred = '';
    const dataTransfer = {
      // eslint-disable-next-line @typescript-eslint/no-unused-vars
      getData: (_format: string) => {
        return dataTransferred;
      },
      setData: (_format: string, data: string) => {
        dataTransferred = data;
      },
    };

    expect(await screen.findByText('Sku')).toBeInTheDocument();
    // Move 2nd item after 4th one
    fireEvent.mouseDown(screen.getAllByTestId('dragAndDrop')[1]);
    fireEvent.dragStart(screen.getAllByRole('row')[1], {dataTransfer});
    fireEvent.dragEnter(screen.getAllByRole('row')[2], {dataTransfer});
    fireEvent.dragLeave(screen.getAllByRole('row')[2], {dataTransfer});
    fireEvent.dragEnter(screen.getAllByRole('row')[3], {dataTransfer});
    fireEvent.drop(screen.getAllByRole('row')[3], {dataTransfer});
    fireEvent.dragEnd(screen.getAllByRole('row')[1], {dataTransfer});

    expect(onChange).toHaveBeenCalledWith([
      {type: CONDITION_NAMES.ENABLED, value: false},
      {type: CONDITION_NAMES.ENABLED, value: true},
      {type: CONDITION_NAMES.ENABLED, value: true},
      {type: CONDITION_NAMES.ENABLED, value: false},
    ]);
  });

  it('should display errors', () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });

    const onChange = jest.fn();
    const validationErrors = [{path: 'conditions', message: 'should contain only 1 enabled'}];
    render(<SelectionTab target={'sku'} conditions={[]} onChange={onChange} validationErrors={validationErrors} />);

    expect(screen.getByText('should contain only 1 enabled')).toBeInTheDocument();
  });
});
