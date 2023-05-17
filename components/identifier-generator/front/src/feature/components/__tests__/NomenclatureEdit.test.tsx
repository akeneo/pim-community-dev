import React from 'react';
import {NomenclatureEdit} from '../';
import {act, fireEvent, render, screen} from '../../tests/test-utils';
import {
  AbbreviationType,
  CanUseNomenclatureProperty,
  Nomenclature,
  Operator,
  PROPERTY_NAMES,
  SimpleSelectProperty,
} from '../../models';
import {NotificationLevel} from '@akeneo-pim-community/shared';
import {waitFor} from '@testing-library/react';
import {mockedUserContext} from '../../mocks/contexts';

jest.mock('../NomenclatureValuesDisplayFilter');
jest.mock('../OperatorSelector');

const mockNotify = jest.fn();


jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useTranslate: () => (i18nKey: string) => i18nKey,
  useRouter: () => ({
    generate: (key: string) => key,
  }),
  useNotify: () => mockNotify,
  useUserContext: () => mockedUserContext,
}));

function updateValue(sourceValue: string, endValue: string) {
  expect(screen.getByTitle(sourceValue)).toBeInTheDocument();
  const input = screen.getByTitle(sourceValue);
  fireEvent.change(input, {target: {value: endValue}});
}

async function updateFilter(sourceFilter: string, endFilter: string) {
  expect(screen.getByText('NomenclatureValuesDisplayFilterMock')).toBeInTheDocument();
  expect(screen.getByText(`Filter = ${sourceFilter}`)).toBeInTheDocument();
  fireEvent.click(screen.getByText(`Filter with ${endFilter}`));
  expect(await screen.findByText(`Filter = ${endFilter}`)).toBeInTheDocument();
}

async function familiesShouldBeInTheDocument(families: string[]) {
  const allFamilies = ['Family1', 'Family2', 'Family3'];
  // eslint-disable-next-line @typescript-eslint/no-for-in-array
  for (const i in allFamilies) {
    if (families.includes(allFamilies[i])) {
      expect(await screen.findByText(allFamilies[i])).toBeInTheDocument();
    } else {
      expect(screen.queryByText(allFamilies[i])).not.toBeInTheDocument();
    }
  }
}

async function updateOperator(sourceOperator: string, endOperator: string) {
  expect(screen.getByText('OperatorSelectorMock')).toBeInTheDocument();
  expect(screen.getByText(`Operator = ${sourceOperator}`)).toBeInTheDocument();
  fireEvent.click(screen.getByText(`Change operator to ${endOperator}`));
  expect(await screen.findByText(`Operator = ${endOperator}`)).toBeInTheDocument();
}

const selectedProperty: CanUseNomenclatureProperty = {
  type: PROPERTY_NAMES.FAMILY,
  process: {
    type: AbbreviationType.NO,
  },
};

describe('NomenclatureEdit', () => {
  it('should render the family codes, labels and nomenclatures', async () => {
    render(<NomenclatureEdit selectedProperty={selectedProperty} />);
    fireEvent.click(screen.getByText('pim_identifier_generator.nomenclature.edit'));

    expect(await screen.findByText('Family1')).toBeInTheDocument();
    expect(await screen.findByText('Family2')).toBeInTheDocument();
    expect(await screen.findByText('Family3')).toBeInTheDocument();
    expect(await screen.findByText('Family1 label')).toBeInTheDocument();
    expect(await screen.findByText('Family2 label')).toBeInTheDocument();
    expect(await screen.findByText('Family3 label')).toBeInTheDocument();
    expect(screen.getByTitle('FA1')).toBeInTheDocument();
    expect(screen.getByTitle('FA2')).toBeInTheDocument();
    expect(screen.getByText('pim_identifier_generator.nomenclature.helper')).toBeVisible();
  });

  it('should navigate with invalid values', async () => {
    render(<NomenclatureEdit selectedProperty={selectedProperty} />);
    // ['FA1', 'FA2', 'fam' (placeholder)], = 3 chars, display all
    fireEvent.click(screen.getByText('pim_identifier_generator.nomenclature.edit'));
    expect(await screen.findByText('Family0')).toBeInTheDocument();

    // Update 1 valid valid to too short value
    // ['FA1', 'FAM2', 'fam' (placeholder)], = 3 chars, display all
    updateValue('FA2', 'F');
    expect(screen.getByTitle('F')).toBeInvalid();

    // Filter with errors
    // ['FA1', 'FAM2', 'fam' (placeholder)], = 3 chars, display errors
    await updateFilter('all', 'error');

    // Only errored value is displayed
    // Valid ones: ['FA1', 'fam']
    await familiesShouldBeInTheDocument(['Family2']);

    // Update value
    // ['FA1', 'FAM2', 'fami' (placeholder)], = 4 chars, display errors
    fireEvent.change(screen.getByTitle('3'), {target: {value: '4'}});

    // Only errored values is displayed
    // Valid ones: ['fami']
    await familiesShouldBeInTheDocument(['Family1', 'Family2']);

    // Update operator
    // ['FA1', 'FAM2', 'fami' (placeholder)], <= 4 chars, display errors
    await updateOperator('=', '<=');

    // Only errored values is displayed
    // Valid ones: ['FA1', 'FAM2', 'fami']
    await familiesShouldBeInTheDocument([]);

    // Untick generate if empty
    // ['FA1', 'FAM2', ''] = 4 chars, display errors
    fireEvent.click(screen.getByText('pim_identifier_generator.nomenclature.generate_if_empty'));
    await updateOperator('<=', '=');
    await updateFilter('error', 'error');

    // Only errored values is displayed
    // Valid ones: []
    await familiesShouldBeInTheDocument(['Family1', 'Family2', 'Family3']);
  });

  it('should navigate with filters', async () => {
    render(<NomenclatureEdit selectedProperty={selectedProperty} />);
    fireEvent.click(screen.getByText('pim_identifier_generator.nomenclature.edit'));
    expect(await screen.findByText('Family1')).toBeInTheDocument();

    await updateFilter('all', 'filled');
    await familiesShouldBeInTheDocument(['Family1', 'Family2']);
    await updateFilter('filled', 'empty');
    await familiesShouldBeInTheDocument(['Family3']);
  });

  it('should use pagination', async () => {
    render(<NomenclatureEdit selectedProperty={selectedProperty} itemsPerPage={2} />);
    fireEvent.click(screen.getByText('pim_identifier_generator.nomenclature.edit'));
    expect(await screen.findByText('Family1')).toBeInTheDocument();

    await familiesShouldBeInTheDocument(['Family0', 'Family1']);
    fireEvent.click(screen.getAllByTitle('No. 2')[0]);
    await familiesShouldBeInTheDocument(['Family2', 'Family3']);
  });

  it('should search', async () => {
    render(<NomenclatureEdit selectedProperty={selectedProperty} />);
    // ['FA1', 'FA2', 'fam' (placeholder)], = 3 chars, display all
    fireEvent.click(screen.getByText('pim_identifier_generator.nomenclature.edit'));
    expect(await screen.findByText('Family1')).toBeInTheDocument();

    updateValue('FA2', 'foo');

    await familiesShouldBeInTheDocument(['Family1', 'Family2', 'Family3']);

    fireEvent.change(await screen.findByPlaceholderText('pim_common.search'), {target: {value: 'FOO'}});
    await familiesShouldBeInTheDocument(['Family2']);

    fireEvent.change(await screen.findByPlaceholderText('pim_common.search'), {target: {value: 'FA'}});
    await familiesShouldBeInTheDocument(['Family1', 'Family2', 'Family3']);

    fireEvent.change(await screen.findByPlaceholderText('pim_common.search'), {target: {value: 'BAZ'}});
    await familiesShouldBeInTheDocument([]);
  });

  it('should save', async () => {
    render(<NomenclatureEdit selectedProperty={selectedProperty} />);
    await waitFor(() => {
      expect(screen.getByText('pim_identifier_generator.nomenclature.edit')).toBeInTheDocument();
    });
    fireEvent.click(screen.getByText('pim_identifier_generator.nomenclature.edit'));
    expect(await screen.findByText('Family1')).toBeInTheDocument();

    await act(async () => {
      fireEvent.click(await screen.findByText('pim_common.save'));
    });

    await waitFor(() => {
      expect(screen.queryByText('pim_identifier_generator.nomenclature.section_title')).not.toBeInTheDocument();
    });

    expect(mockNotify).toHaveBeenCalled();
    expect(mockNotify).toHaveBeenCalledWith(
      NotificationLevel.SUCCESS,
      'pim_identifier_generator.nomenclature.flash.success'
    );
  });

  it('should save with warnings', async () => {
    render(<NomenclatureEdit selectedProperty={selectedProperty} />);
    fireEvent.click(screen.getByText('pim_identifier_generator.nomenclature.edit'));
    expect(await screen.findByText('Family1')).toBeInTheDocument();

    updateValue('FA2', 'A');
    await updateFilter('all', 'error');
    await familiesShouldBeInTheDocument(['Family2']);

    await act(async () => {
      fireEvent.click(await screen.findByText('pim_common.save'));
    });

    await waitFor(() => expect(mockNotify).toHaveBeenCalled());
    expect(mockNotify).toHaveBeenCalledWith(
      NotificationLevel.WARNING,
      'pim_identifier_generator.nomenclature.flash.warning'
    );
  });

  it('should not save when violation errors', async () => {
    const mockConsole = jest.spyOn(console, 'error').mockImplementation();

    render(<NomenclatureEdit selectedProperty={selectedProperty} />);
    fireEvent.click(screen.getByText('pim_identifier_generator.nomenclature.edit'));
    expect(await screen.findByText('Family1')).toBeInTheDocument();

    fireEvent.change(screen.getByTitle('3'), {target: {value: ''}});

    await act(async () => {
      fireEvent.click(await screen.findByText('pim_common.save'));
    });

    await waitFor(() => expect(mockNotify).toHaveBeenCalled());
    expect(mockNotify).toHaveBeenCalledWith(
      NotificationLevel.ERROR,
      'pim_identifier_generator.nomenclature.flash.error'
    );

    mockConsole.mockRestore();
  });

  it('should render the simple select codes, labels and nomenclatures', async () => {
    const defaultSimpleSelect = [
      {code: 'black', labels: {en_US: 'Black label'}},
      {code: 'white', labels: {}},
      {code: 'red', labels: {fr_FR: 'Rouge'}},
    ];

    const nomenclatureSimpleSelect: Nomenclature = {
      propertyCode: 'color',
      operator: Operator.EQUALS,
      value: 3,
      generate_if_empty: true,
      values: {
        black: 'BLA',
        white: 'WHI',
      },
    };

    const fetchImplementation = jest.fn().mockImplementation((requestUrl: string) => {
      if (requestUrl === 'akeneo_identifier_generator_nomenclature_rest_get') {
        return Promise.resolve({
          ok: true,
          json: () => Promise.resolve(nomenclatureSimpleSelect),
          status: 200,
        } as Response);
      } else if (requestUrl === 'akeneo_identifier_generator_get_attribute_options') {
        return Promise.resolve({
          ok: true,
          json: () => Promise.resolve(defaultSimpleSelect),
          status: 200,
        } as Response);
      }
      throw new Error(`Unknown url ${JSON.stringify(requestUrl)}`);
    });
    jest.spyOn(global, 'fetch').mockImplementation(fetchImplementation);

    const selectedPropertySimpleSelect: SimpleSelectProperty = {
      attributeCode: 'color',
      type: PROPERTY_NAMES.SIMPLE_SELECT,
      process: {
        type: AbbreviationType.NO,
      },
    };

    render(<NomenclatureEdit selectedProperty={selectedPropertySimpleSelect} />);
    fireEvent.click(screen.getByText('pim_identifier_generator.nomenclature.edit'));

    expect(await screen.findByText('pim_identifier_generator.nomenclature.edit')).toBeInTheDocument();
    expect(await screen.findByText('black')).toBeInTheDocument();
    expect(await screen.findByText('white')).toBeInTheDocument();
    expect(await screen.findByText('red')).toBeInTheDocument();
    expect(await screen.findByText('Black label')).toBeInTheDocument();
    expect(await screen.findByText('[white]')).toBeInTheDocument();
    expect(await screen.findByText('[red]')).toBeInTheDocument();
    expect(screen.getByTitle('BLA')).toBeInTheDocument();
    expect(screen.getByTitle('WHI')).toBeInTheDocument();
    expect(screen.getByText('pim_identifier_generator.nomenclature.helper')).toBeVisible();
  });
});
