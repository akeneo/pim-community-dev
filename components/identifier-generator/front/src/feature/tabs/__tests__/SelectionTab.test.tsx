import React from 'react';
import {fireEvent, mockResponse, render, screen} from '../../tests/test-utils';
import {SelectionTab} from '../SelectionTab';
import {
  CONDITION_NAMES,
  IdentifierGenerator,
  Operator,
  SimpleOrMultiSelectCondition,
  TEXT_TRANSFORMATION,
} from '../../models';
import {CategoriesCondition} from '../../models/conditions/categoriesCondition';

jest.mock('../conditions/AddConditionButton');
jest.mock('../conditions/CategoriesLine');
jest.mock('../conditions/EnabledLine');
jest.mock('../conditions/SimpleOrMultiSelectLine');
jest.mock('../../pages/SimpleDeleteModal');

const mockedGenerator: IdentifierGenerator = {
  target: 'sku',
  conditions: [],
  structure: [],
  code: 'identifier-generator',
  delimiter: null,
  labels: {},
  text_transformation: TEXT_TRANSFORMATION.NO,
};

describe('SelectionTab', () => {
  it('should render the selection tab', () => {
    render(<SelectionTab generator={mockedGenerator} onChange={jest.fn()} validationErrors={[]} />);

    expect(screen.getByText('pim_identifier_generator.tabs.product_selection')).toBeInTheDocument();
  });

  it('should render the default identifier attribute', async () => {
    const expectCall = mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });

    render(<SelectionTab generator={mockedGenerator} onChange={jest.fn()} validationErrors={[]} />);

    expect(await screen.findByText('Sku')).toBeInTheDocument();

    expectCall();
  });

  it('should render the default identifier attribute with target as attribute', async () => {
    const expectCall = mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [],
    });

    render(<SelectionTab generator={mockedGenerator} onChange={jest.fn()} validationErrors={[]} />);

    expect(await screen.findByText('[sku]')).toBeInTheDocument();

    expectCall();
  });

  it('should callback on change', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });

    const onChange = jest.fn();
    const generator: IdentifierGenerator = {
      ...mockedGenerator,
      conditions: [
        {type: CONDITION_NAMES.ENABLED, value: true},
        {type: CONDITION_NAMES.FAMILY, operator: Operator.EMPTY},
      ],
    };
    render(<SelectionTab generator={generator} onChange={onChange} validationErrors={[]} />);

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
    render(<SelectionTab generator={mockedGenerator} onChange={onChange} validationErrors={[]} />);

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

    render(<SelectionTab generator={mockedGenerator} onChange={onChange} validationErrors={[]} />);
    expect(await screen.findByText('Sku')).toBeInTheDocument();
    expect(screen.queryByText('pim_identifier_generator.selection.empty.title')).toBeInTheDocument();
  });

  it('should not display placeholder there is at least 1 condition', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });
    const onChange = jest.fn();
    const generator: IdentifierGenerator = {
      ...mockedGenerator,
      conditions: [{type: CONDITION_NAMES.ENABLED, value: true}],
    };
    render(<SelectionTab generator={generator} onChange={onChange} validationErrors={[]} />);
    expect(await screen.findByText('Sku')).toBeInTheDocument();
    expect(screen.queryByText('pim_identifier_generator.selection.empty.title')).not.toBeInTheDocument();
  });

  it('should delete a condition', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });

    const onChange = jest.fn();
    const generator: IdentifierGenerator = {
      ...mockedGenerator,
      conditions: [{type: CONDITION_NAMES.ENABLED, value: true}],
    };
    render(<SelectionTab generator={generator} onChange={onChange} validationErrors={[]} />);

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
    const generator: IdentifierGenerator = {
      ...mockedGenerator,
      conditions: [{type: CONDITION_NAMES.ENABLED, value: true}],
    };
    const onChange = jest.fn();
    render(<SelectionTab generator={generator} onChange={onChange} validationErrors={[]} />);

    expect(await screen.findByText('Sku')).toBeInTheDocument();

    expect(screen.getByText('EnabledLineMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Delete Enabled'));
    expect(screen.getByText('SimpleDeleteModalMock')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Close modal'));
    expect(screen.queryByText('SimpleDeleteModalMock')).not.toBeInTheDocument();
    expect(onChange).not.toBeCalledWith([]);
  });

  it('should display simple select line', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });
    const conditions = [
      {
        type: CONDITION_NAMES.SIMPLE_SELECT,
        value: [],
        operator: Operator.IN,
        scope: null,
        locale: null,
        attributeCode: 'simple_select',
      } as SimpleOrMultiSelectCondition,
    ];
    const generator: IdentifierGenerator = {...mockedGenerator, conditions};

    const screen = render(<SelectionTab generator={generator} onChange={jest.fn()} validationErrors={[]} />);

    expect(await screen.findByText('SimpleOrMultiSelectLineMock')).toBeInTheDocument();
  });

  it('should display multi select line', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });
    const conditions = [
      {
        type: CONDITION_NAMES.MULTI_SELECT,
        value: [],
        operator: Operator.IN,
        scope: null,
        locale: null,
        attributeCode: 'multi_select',
      } as SimpleOrMultiSelectCondition,
    ];
    const generator: IdentifierGenerator = {...mockedGenerator, conditions};

    const screen = render(<SelectionTab generator={generator} onChange={jest.fn()} validationErrors={[]} />);

    expect(await screen.findByText('SimpleOrMultiSelectLineMock')).toBeInTheDocument();
  });

  it('should display categories line', async () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });
    const conditions = [
      {
        type: CONDITION_NAMES.CATEGORIES,
        value: [],
        operator: Operator.IN,
      } as CategoriesCondition,
    ];
    const generator: IdentifierGenerator = {...mockedGenerator, conditions};

    const screen = render(<SelectionTab generator={generator} onChange={jest.fn()} validationErrors={[]} />);

    expect(await screen.findByText('CategoriesLineMock')).toBeInTheDocument();
  });

  it('should display errors', () => {
    mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });

    const onChange = jest.fn();
    const validationErrors = [{path: 'conditions', message: 'should contain only 1 enabled'}];
    render(<SelectionTab generator={mockedGenerator} onChange={onChange} validationErrors={validationErrors} />);

    expect(screen.getByText('should contain only 1 enabled')).toBeInTheDocument();
  });
});
