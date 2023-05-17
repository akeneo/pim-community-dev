import React, {ReactNode} from 'react';
import {render, waitFor} from '../../../tests/test-utils';
import {SimpleOrMultiSelectLine} from '../SimpleOrMultiSelectLine';
import {CONDITION_NAMES, Operator, SimpleOrMultiSelectCondition} from '../../../models';
import {fireEvent} from '@testing-library/react';
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
      expect(screen.getByText('[Option1]')).toBeInTheDocument();
      fireEvent.click(screen.getByText('[Option1]'));
    });

    expect(mockedOnChange).toHaveBeenCalledWith({
      ...condition,
      value: ['Option1'],
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
    const condition: SimpleOrMultiSelectCondition = {
      attributeCode: 'simple_select_localizable',
      type: CONDITION_NAMES.SIMPLE_SELECT,
      value: ['Option1'],
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
      expect(screen.getByText('Option1')).toBeInTheDocument();
    });
    await waitFor(() => expect(screen.getByPlaceholderText('pim_common.locale')).toBeInTheDocument());
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
    const condition: SimpleOrMultiSelectCondition = {
      attributeCode: 'simple_select_scopable',
      type: CONDITION_NAMES.SIMPLE_SELECT,
      value: ['Option1'],
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
      expect(screen.getByText('Option1')).toBeInTheDocument();
    });
    await waitFor(() => expect(screen.getByPlaceholderText('pim_common.channel')).toBeInTheDocument());
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
    const condition: SimpleOrMultiSelectCondition = {
      attributeCode: 'unauthorized_attribute',
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
    const condition: SimpleOrMultiSelectCondition = {
      attributeCode: 'unknown_attribute',
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
    const condition: SimpleOrMultiSelectCondition = {
      attributeCode: 'deleted_attribute',
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
