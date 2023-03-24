import React, {ReactNode} from 'react';
import {render, waitFor} from '../../../tests/test-utils';
import {SimpleOrMultiSelectLine} from '../SimpleOrMultiSelectLine';
import {CONDITION_NAMES, Operator, SimpleOrMultiSelectCondition} from '../../../models';
import {fireEvent} from '@testing-library/react';
import mockedScopes from '../../../tests/fixtures/scopes';
import {useSecurity} from '@akeneo-pim-community/shared';

const TableMock = ({children}: {children: ReactNode}) => (
  <table>
    <tbody>
      <tr>{children}</tr>
    </tbody>
  </table>
);

describe('SimpleOrMultiSelectLine', () => {
  it('displays select options for IN and NOT_IN operator', async () => {
    //GIVEN a simple select WIth IN condition
    const condition: SimpleOrMultiSelectCondition = {
      attributeCode: 'simple_select',
      type: CONDITION_NAMES.SIMPLE_SELECT,
      value: [],
      operator: Operator.IN,
      scope: null,
      locale: null,
    };

    mockSimpleSelectCalls();

    const mockedOnChange = jest.fn();
    const screen = render(
      <TableMock>
        <SimpleOrMultiSelectLine condition={condition} onChange={mockedOnChange} onDelete={jest.fn()} />
      </TableMock>
    );

    //THEN I can see the list of all available options,
    await waitFor(() => {
      expect(
        screen.getByPlaceholderText('pim_identifier_generator.selection.settings.select_option.placeholder')
      ).toBeInTheDocument();
      fireEvent.click(screen.getAllByTitle('pim_common.open')[1]);
    });

    await waitFor(() => {
      expect(screen.getByText('OptionA')).toBeInTheDocument();
      fireEvent.click(screen.getByText('OptionA'));
    });

    expect(mockedOnChange).toHaveBeenCalledWith({
      ...condition,
      value: ['option_a'],
    });

    fireEvent.click(screen.getAllByTitle('pim_common.open')[0]);
    fireEvent.click(screen.getByText('pim_common.operators.NOT IN'));

    expect(mockedOnChange).toHaveBeenCalledWith({
      ...condition,
      operator: Operator.NOT_IN,
    });

    expect(screen.queryByPlaceholderText('pim_common.locale')).not.toBeInTheDocument();
    expect(screen.queryByPlaceholderText('pim_common.channel')).not.toBeInTheDocument();
  });

  it('does not display select options for EMPTY operator', async () => {
    //GIVEN a simple select WIth IN condition
    const condition: SimpleOrMultiSelectCondition = {
      attributeCode: 'simple_select',
      type: CONDITION_NAMES.SIMPLE_SELECT,
      value: [],
      operator: Operator.EMPTY,
      scope: null,
      locale: null,
    };

    mockSimpleSelectCalls();

    const mockedOnChange = jest.fn();
    const screen = render(
      <TableMock>
        <SimpleOrMultiSelectLine condition={condition} onChange={mockedOnChange} onDelete={jest.fn()} />
      </TableMock>
    );

    //THEN I can not see the list of all available options,
    await waitFor(() => {
      expect(screen.getByText('pim_common.operators.EMPTY')).toBeInTheDocument();
    });

    expect(
      screen.queryByPlaceholderText('pim_identifier_generator.selection.settings.select_option.placeholder')
    ).not.toBeInTheDocument();
    fireEvent.click(screen.getAllByTitle('pim_common.open')[0]);
    fireEvent.click(screen.getByText('pim_common.operators.NOT EMPTY'));

    expect(mockedOnChange).toHaveBeenCalledWith({
      attributeCode: condition.attributeCode,
      locale: null,
      operator: Operator.NOT_EMPTY,
      scope: null,
      type: condition.type,
    });
    expect(screen.queryByPlaceholderText('pim_common.locale')).not.toBeInTheDocument();
    expect(screen.queryByPlaceholderText('pim_common.channel')).not.toBeInTheDocument();
  });

  it('displays locale selector when simple select is localizable', async () => {
    //GIVEN a localizable simple select added in the list of conditions
    mockSimpleSelectCalls({localizable: true});

    const condition: SimpleOrMultiSelectCondition = {
      attributeCode: 'simple_select',
      type: CONDITION_NAMES.SIMPLE_SELECT,
      value: ['option_a'],
      operator: Operator.IN,
      scope: null,
      locale: null,
    };
    const mockedOnChange = jest.fn();
    const screen = render(
      <TableMock>
        <SimpleOrMultiSelectLine condition={condition} onChange={mockedOnChange} onDelete={jest.fn()} />
      </TableMock>
    );

    //THEN I am invited to choose the locale
    await waitFor(() => {
      expect(screen.getByText('OptionA')).toBeInTheDocument();
    });
    expect(screen.getByPlaceholderText('pim_common.locale')).toBeInTheDocument();
    expect(screen.queryByPlaceholderText('pim_common.channel')).not.toBeInTheDocument();

    fireEvent.click(screen.getByTitle('pim_common.locale'));
    expect(screen.getByText('French (France)')).toBeInTheDocument();
    fireEvent.click(screen.getByText('French (France)'));
    expect(mockedOnChange).toHaveBeenCalledWith({
      ...condition,
      locale: 'fr_FR',
    });
  });

  it('displays channel selector when simple select is scopable', async () => {
    //GIVEN a scopable simple select added in the list of conditions
    mockSimpleSelectCalls({scopable: true});

    const condition: SimpleOrMultiSelectCondition = {
      attributeCode: 'simple_select',
      type: CONDITION_NAMES.SIMPLE_SELECT,
      value: ['option_a'],
      operator: Operator.IN,
      scope: null,
      locale: null,
    };
    const mockedOnChange = jest.fn();
    const screen = render(
      <TableMock>
        <SimpleOrMultiSelectLine condition={condition} onChange={mockedOnChange} onDelete={jest.fn()} />
      </TableMock>
    );

    //THEN I am invited to choose the channel
    await waitFor(() => {
      expect(screen.getByText('OptionA')).toBeInTheDocument();
    });
    expect(screen.getByPlaceholderText('pim_common.channel')).toBeInTheDocument();
    expect(screen.queryByPlaceholderText('pim_common.locale')).not.toBeInTheDocument();

    fireEvent.click(screen.getByTitle('pim_common.channel'));
    expect(screen.getByText('Ecommerce')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Ecommerce'));
    expect(mockedOnChange).toHaveBeenCalledWith({
      ...condition,
      scope: 'ecommerce',
    });
  });

  it('displays unauthorized error when user is not authorized to see attribute', async () => {
    mockSimpleSelectCalls({inError: true, errorStatus: '401'});

    const condition: SimpleOrMultiSelectCondition = {
      attributeCode: 'unknown_simple_select',
      type: CONDITION_NAMES.SIMPLE_SELECT,
      value: ['option_a'],
      operator: Operator.IN,
      scope: null,
      locale: null,
    };
    const mockedOnChange = jest.fn();
    const screen = render(
      <TableMock>
        <SimpleOrMultiSelectLine condition={condition} onChange={mockedOnChange} onDelete={jest.fn()} />
      </TableMock>
    );

    await waitFor(() => {
      expect(screen.getByText('pim_error.unauthorized_list_attributes')).toBeInTheDocument();
    });
  });

  it('displays general error when attribute route fails', async () => {
    mockSimpleSelectCalls({inError: true, errorStatus: '500'});

    const condition: SimpleOrMultiSelectCondition = {
      attributeCode: 'unknown_simple_select',
      type: CONDITION_NAMES.SIMPLE_SELECT,
      value: ['option_a'],
      operator: Operator.IN,
      scope: null,
      locale: null,
    };
    const mockedOnChange = jest.fn();
    const screen = render(
      <TableMock>
        <SimpleOrMultiSelectLine condition={condition} onChange={mockedOnChange} onDelete={jest.fn()} />
      </TableMock>
    );

    await waitFor(() => {
      expect(screen.getByText('pim_error.general')).toBeInTheDocument();
    });
  });

  it('displays custom error when attribute was deleted', async () => {
    mockSimpleSelectCalls({inError: true, errorStatus: '404'});

    const condition: SimpleOrMultiSelectCondition = {
      attributeCode: 'deleted_simple_select',
      type: CONDITION_NAMES.SIMPLE_SELECT,
      value: ['option_a'],
      operator: Operator.IN,
      scope: null,
      locale: null,
    };
    const screen = render(
      <TableMock>
        <SimpleOrMultiSelectLine condition={condition} onChange={jest.fn()} onDelete={jest.fn()} />
      </TableMock>
    );

    await waitFor(() => {
      expect(screen.getByText('pim_error.selection_attribute_not_found')).toBeInTheDocument();
    });
  });

  it('displays info message when user has no right to list attributes', async () => {
    mockSimpleSelectCalls();
    (useSecurity as jest.Mock).mockImplementation(() => ({
      isGranted: (acl: string) =>
        ({
          pim_enrich_attribute_index: false,
        }[acl] ?? false),
    }));
    const condition: SimpleOrMultiSelectCondition = {
      attributeCode: 'simple_select',
      type: CONDITION_NAMES.SIMPLE_SELECT,
      value: [],
      operator: Operator.IN,
      scope: null,
      locale: null,
    };

    const screen = render(
      <TableMock>
        <SimpleOrMultiSelectLine condition={condition} onChange={jest.fn()} onDelete={jest.fn()} />
      </TableMock>
    );

    await waitFor(() => {
      expect(screen.getByText('pim_error.unauthorized_list_properties')).toBeInTheDocument();
    });
  });
});

const mockSimpleSelectCalls = ({localizable = false, scopable = false, inError = false, errorStatus = ''} = {}) => {
  const fetchImplementation = jest.fn().mockImplementation((requestUrl: string, args: {method: string}) => {
    if (requestUrl === 'akeneo_identifier_generator_get_attribute_options') {
      return Promise.resolve({
        ok: true,
        json: () =>
          Promise.resolve([
            {code: 'option_a', labels: {en_US: 'OptionA'}},
            {code: 'option_b', labels: {en_US: 'OptionB'}},
            {code: 'option_c', labels: {en_US: 'OptionC'}},
            {code: 'option_d', labels: {en_US: 'OptionD'}},
            {code: 'option_e', labels: {en_US: 'OptionE'}},
          ]),
        statusText: '',
        status: 200,
      } as Response);
    } else if (requestUrl === 'pim_enrich_channel_rest_index') {
      return Promise.resolve({
        ok: true,
        json: () => Promise.resolve(mockedScopes),
        statusText: '',
        status: 200,
      } as Response);
    } else if (requestUrl === 'pim_enrich_attribute_rest_get') {
      if (inError) {
        jest.spyOn(console, 'error');
        // eslint-disable-next-line no-console
        (console.error as jest.Mock).mockImplementation(() => null);
        return Promise.resolve({
          ok: false,
          json: () => Promise.resolve(),
          statusText: errorStatus,
          status: Number.parseFloat(errorStatus),
        } as Response);
      } else {
        return Promise.resolve({
          ok: true,
          json: () =>
            Promise.resolve({
              code: 'simple_select',
              labels: {en_US: 'Simple select', fr_FR: 'Select simple'},
              localizable,
              scopable,
            }),
          statusText: '',
          status: 200,
        } as Response);
      }
    }

    throw new Error(`Unmocked url "${requestUrl}" [${args.method}]`);
  });
  jest.spyOn(global, 'fetch').mockImplementation(fetchImplementation);
};
