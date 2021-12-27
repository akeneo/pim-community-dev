import React from 'react';
import 'jest-fetch-mock';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import SelectInput from '../../../../src/product/CellInputs/SelectInput';
import {ColumnDefinition, SelectColumnDefinition, SelectOption, SelectOptionRepository} from '../../../../src';
import {getComplexTableAttribute} from '../../../factories';
import {nutritionScoreSelectOptions} from '../../../../src/fetchers/__mocks__/SelectOptionsFetcher';
import {TestAttributeContextProvider} from '../../../shared/TestAttributeContextProvider';
import {mockScroll} from '../../../shared/mockScroll';

jest.mock('../../../../src/attribute/ManageOptionsModal');
const scroll = mockScroll();

const fetchGetSelectOptions = (options: SelectOption[]) => {
  fetchMock.mockResponse((request: Request) => {
    if (request.url.includes('pim_table_attribute_get_select_options')) {
      return Promise.resolve(JSON.stringify(options));
    }

    throw new Error(`The "${request.url}" url is not mocked.`);
  });
};

const nutritionScoreColumn: ColumnDefinition = {
  code: 'nutrition_score',
  validations: {},
  data_type: 'select',
  labels: {en_US: 'Nutrition score'},
};

const getComplexAttributeWithOptions = (options = nutritionScoreSelectOptions) => {
  const tableAttribute = getComplexTableAttribute();
  tableAttribute.table_configuration[4] = {
    ...(tableAttribute.table_configuration[4] as SelectColumnDefinition),
    options,
  };
  return tableAttribute;
};

describe('SelectInput', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    SelectOptionRepository.clearCache();
  });

  it('should render label of existing option', async () => {
    fetchGetSelectOptions(nutritionScoreSelectOptions);

    renderWithProviders(
      <SelectInput
        columnDefinition={nutritionScoreColumn}
        highlighted={false}
        inError={false}
        row={{'unique id': 'uniqueIdB', nutrition_score: 'B'}}
        onChange={jest.fn()}
        attribute={getComplexAttributeWithOptions()}
        setAttribute={jest.fn()}
      />
    );

    expect(await screen.findByText('B')).toBeInTheDocument();
  });

  it('should delete the value', async () => {
    fetchGetSelectOptions(nutritionScoreSelectOptions);

    const handleChange = jest.fn();
    renderWithProviders(
      <SelectInput
        columnDefinition={nutritionScoreColumn}
        highlighted={false}
        inError={false}
        row={{'unique id': 'uniqueIdB', nutrition_score: 'B'}}
        onChange={handleChange}
        attribute={getComplexAttributeWithOptions()}
        setAttribute={jest.fn()}
      />
    );
    expect(await screen.findByText('B')).toBeInTheDocument();

    fireEvent.click(screen.getByTitle('pim_common.clear'));
    expect(handleChange).toBeCalledWith(undefined);
  });

  it('should display nothing if no options', () => {
    fetchGetSelectOptions([]);

    const handleChange = jest.fn();
    renderWithProviders(
      <SelectInput
        columnDefinition={{...nutritionScoreColumn, code: 'no_options'}}
        highlighted={false}
        inError={false}
        row={{'unique id': 'uniqueIdB', no_options: 'B'}}
        onChange={handleChange}
        attribute={getComplexAttributeWithOptions()}
        setAttribute={jest.fn()}
      />
    );

    expect(screen.queryByText('B')).not.toBeInTheDocument();
  });

  it('should paginate the options', async () => {
    fetchGetSelectOptions(nutritionScoreSelectOptions);

    renderWithProviders(
      <SelectInput
        columnDefinition={nutritionScoreColumn}
        highlighted={false}
        inError={false}
        row={{'unique id': 'uniqueIdB', nutrition_score: 'B'}}
        onChange={jest.fn()}
        attribute={getComplexAttributeWithOptions()}
        setAttribute={jest.fn()}
      />
    );
    expect(await screen.findByText('B')).toBeInTheDocument();

    await act(async () => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
      expect(await screen.findByText('A')).toBeInTheDocument();
    });

    expect(screen.queryByText('U')).not.toBeInTheDocument();

    act(() => scroll());
    expect(await screen.findByText('U')).toBeInTheDocument();
  });

  it('should updates the value', async () => {
    fetchGetSelectOptions(nutritionScoreSelectOptions);
    const handleChange = jest.fn();

    renderWithProviders(
      <SelectInput
        columnDefinition={nutritionScoreColumn}
        highlighted={false}
        inError={false}
        row={{'unique id': 'uniqueIdB', nutrition_score: 'B'}}
        onChange={handleChange}
        attribute={getComplexAttributeWithOptions()}
        setAttribute={jest.fn()}
      />
    );
    expect(await screen.findByText('B')).toBeInTheDocument();

    await act(async () => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
      expect(await screen.findByText('A')).toBeInTheDocument();
    });

    fireEvent.click(screen.getByText('A'));
    expect(handleChange).toBeCalledWith('A');
  });

  it('should search in the options', async () => {
    fetchGetSelectOptions(nutritionScoreSelectOptions);

    renderWithProviders(
      <SelectInput
        columnDefinition={nutritionScoreColumn}
        highlighted={false}
        inError={false}
        row={{'unique id': 'uniqueIdB', nutrition_score: 'B'}}
        onChange={jest.fn()}
        attribute={getComplexAttributeWithOptions()}
        setAttribute={jest.fn()}
      />
    );
    expect(await screen.findByText('B')).toBeInTheDocument();

    await act(async () => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
      expect(await screen.findByText('A')).toBeInTheDocument();
      fireEvent.change(screen.getByPlaceholderText('pim_common.search'), {target: {value: 'U'}});
    });

    expect(screen.queryByText('U')).toBeInTheDocument();
    expect(screen.queryByText('A')).not.toBeInTheDocument();

    fireEvent.change(screen.getByPlaceholderText('pim_common.search'), {target: {value: 'foobarz'}});
    expect(await screen.findByText('pim_table_attribute.form.product.no_results')).toBeInTheDocument();
  });

  it('should display a message when there is no option', async () => {
    fetchGetSelectOptions([]);

    renderWithProviders(
      <SelectInput
        columnDefinition={nutritionScoreColumn}
        highlighted={false}
        inError={false}
        row={{'unique id': 'uniqueIdB'}}
        onChange={jest.fn()}
        attribute={getComplexAttributeWithOptions([])}
        setAttribute={jest.fn()}
      />
    );
    expect(await screen.findByTitle('pim_common.open')).toBeInTheDocument();

    await act(async () => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
      expect(await screen.findByText('pim_table_attribute.form.product.no_options')).toBeInTheDocument();
    });
  });

  it('should update options directly', async () => {
    let hasCalledPostAttribute = false;
    let getOptionsCalls = 0;
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_attribute_rest_post')) {
        hasCalledPostAttribute = true;
        return Promise.resolve(JSON.stringify(true));
      }
      if (request.url.includes('pim_table_attribute_get_select_options')) {
        getOptionsCalls++;
        if (getOptionsCalls <= 2) {
          return Promise.resolve(JSON.stringify(nutritionScoreSelectOptions));
        } else {
          return Promise.resolve(JSON.stringify([{code: 'fake_code'}]));
        }
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const setAttribute = jest.fn();

    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <SelectInput
          columnDefinition={nutritionScoreColumn}
          highlighted={false}
          inError={false}
          row={{'unique id': 'uniqueIdB', nutrition_score: 'B'}}
          onChange={jest.fn()}
          attribute={getComplexAttributeWithOptions()}
          setAttribute={setAttribute}
        />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('B')).toBeInTheDocument();
    fireEvent.click(screen.getByTitle('pim_common.open'));
    expect(await screen.findByText('pim_table_attribute.form.attribute.manage_options')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_table_attribute.form.attribute.manage_options'));
    await act(async () => {
      fireEvent.click(await screen.findByText('Fake confirm'));
    });
    expect(hasCalledPostAttribute).toBeTruthy();
    expect(setAttribute).toBeCalled();
  });
});
