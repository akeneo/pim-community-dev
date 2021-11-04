import React from 'react';
import 'jest-fetch-mock';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import SelectInput from '../../../../src/product/CellInputs/SelectInput';
import {ColumnDefinition, SelectOption} from '../../../../src/models';
import {getComplexTableAttribute} from '../../../factories';
import {TestAttributeContextProvider} from '../../../shared/TestAttributeContextProvider';
import {nutritionScoreSelectOptions} from '../../../../src/fetchers/__mocks__/SelectOptionsFetcher';
import {SelectOptionRepository} from '../../../../src/repositories';

jest.mock('../../../../src/attribute/ManageOptionsModal');

type EntryCallback = (entries: {isIntersecting: boolean}[]) => void;

let entryCallback: EntryCallback | undefined = undefined;
const intersectionObserverMock = (callback: EntryCallback) => ({
  observe: jest.fn(() => (entryCallback = callback)),
  unobserve: jest.fn(),
});
window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

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
  labels: {},
};

describe('SelectInput', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    SelectOptionRepository.clearCache();
  });

  it('should render label of existing option', async () => {
    fetchGetSelectOptions(nutritionScoreSelectOptions);

    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <SelectInput
          columnDefinition={nutritionScoreColumn}
          highlighted={false}
          inError={false}
          row={{'unique id': 'uniqueIdB', nutrition_score: 'B'}}
          onChange={jest.fn()}
        />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('B')).toBeInTheDocument();
  });

  it('should delete the value', async () => {
    fetchGetSelectOptions(nutritionScoreSelectOptions);

    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <SelectInput
          columnDefinition={nutritionScoreColumn}
          highlighted={false}
          inError={false}
          row={{'unique id': 'uniqueIdB', nutrition_score: 'B'}}
          onChange={handleChange}
        />
      </TestAttributeContextProvider>
    );
    expect(await screen.findByText('B')).toBeInTheDocument();

    fireEvent.click(screen.getByTitle('pim_common.clear'));
    expect(handleChange).toBeCalledWith(undefined);
  });

  it('should display nothing if no options', () => {
    fetchGetSelectOptions([]);

    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <SelectInput
          columnDefinition={{...nutritionScoreColumn, code: 'no_options'}}
          highlighted={false}
          inError={false}
          row={{'unique id': 'uniqueIdB', no_options: 'B'}}
          onChange={handleChange}
        />
      </TestAttributeContextProvider>
    );

    expect(screen.queryByText('B')).not.toBeInTheDocument();
  });

  it('should paginate the options', async () => {
    fetchGetSelectOptions(nutritionScoreSelectOptions);

    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <SelectInput
          columnDefinition={nutritionScoreColumn}
          highlighted={false}
          inError={false}
          row={{'unique id': 'uniqueIdB', nutrition_score: 'B'}}
          onChange={jest.fn()}
        />
      </TestAttributeContextProvider>
    );
    expect(await screen.findByText('B')).toBeInTheDocument();

    await act(async () => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
      expect(await screen.findByText('A')).toBeInTheDocument();
    });

    expect(screen.queryByText('U')).not.toBeInTheDocument();

    act(() => {
      entryCallback?.([{isIntersecting: true}]);
    });
    expect(await screen.findByText('U')).toBeInTheDocument();
  });

  it('should updates the value', async () => {
    fetchGetSelectOptions(nutritionScoreSelectOptions);

    const handleChange = jest.fn();

    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <SelectInput
          columnDefinition={nutritionScoreColumn}
          highlighted={false}
          inError={false}
          row={{'unique id': 'uniqueIdB', nutrition_score: 'B'}}
          onChange={handleChange}
        />
      </TestAttributeContextProvider>
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
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <SelectInput
          columnDefinition={nutritionScoreColumn}
          highlighted={false}
          inError={false}
          row={{'unique id': 'uniqueIdB', nutrition_score: 'B'}}
          onChange={jest.fn()}
        />
      </TestAttributeContextProvider>
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
    expect(await screen.findByText('pim_table_attribute.form.attribute.please_try_again')).toBeInTheDocument();
  });

  it('should display a link when there is no option', async () => {
    fetchGetSelectOptions([]);

    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <SelectInput
          columnDefinition={nutritionScoreColumn}
          highlighted={false}
          inError={false}
          row={{'unique id': 'uniqueIdB'}}
          onChange={jest.fn()}
        />
      </TestAttributeContextProvider>
    );
    expect(await screen.findByTitle('pim_common.open')).toBeInTheDocument();

    await act(async () => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
      expect(await screen.findByText('pim_table_attribute.form.product.no_add_options_link')).toBeInTheDocument();
      fireEvent.click(screen.getByText('pim_table_attribute.form.product.no_add_options_link'));
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

    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <SelectInput
          columnDefinition={nutritionScoreColumn}
          highlighted={false}
          inError={false}
          row={{'unique id': 'uniqueIdB', nutrition_score: 'B'}}
          onChange={jest.fn()}
        />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('B')).toBeInTheDocument();
    fireEvent.click(screen.getByTitle('pim_common.open'));
    expect(await screen.findByText('Edit options')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Edit options'));
    fireEvent.click(screen.getByText('Fake confirm'));
    expect(hasCalledPostAttribute).toBeTruthy();
    expect(await screen.findByText('[B]')).toBeInTheDocument();
  });
});
