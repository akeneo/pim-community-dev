import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import render, {InputValueProps} from '../../../src/rules/TableValue';
import {Attribute} from '../../../src/models';
import {defaultCellInputsMapping, defaultCellMatchersMapping, getComplexTableAttribute} from '../../factories';

jest.mock('../../../src/attribute/LocaleLabel');
jest.mock('../../../src/fetchers/SelectOptionsFetcher');

const intersectionObserverMock = () => ({
  observe: jest.fn(),
  unobserve: jest.fn(),
});
window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

describe('TableInputValue', () => {
  it('should render the component', async () => {
    const handleChange = jest.fn();
    const props = {
      attribute: getComplexTableAttribute() as Attribute,
      value: [],
      onChange: handleChange,
      id: 'id',
      name: 'name',
      cellInputsMapping: defaultCellInputsMapping,
      cellMatchersMapping: defaultCellMatchersMapping,
    } as InputValueProps;
    renderWithProviders(render(props));

    expect(await screen.findByText('Ingredients')).toBeInTheDocument();

    const button = screen.getByText('pim_table_attribute.product_edit_form.add_rows');
    await act(async () => {
      fireEvent.click(button);
      expect(await screen.findByText('Sugar')).toBeInTheDocument();
    });

    const [saltCheckbox] = screen.getAllByRole('checkbox');

    act(() => {
      fireEvent.click(saltCheckbox);
    });

    expect(await screen.findByText('Salt')).toBeInTheDocument();
    expect(handleChange).toBeCalledWith([{ingredient: 'salt'}]);
  });
});
