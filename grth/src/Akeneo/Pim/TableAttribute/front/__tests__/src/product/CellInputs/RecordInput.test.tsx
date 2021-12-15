import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import RecordInput from '../../../../src/product/CellInputs/RecordInput';
import {getComplexTableAttribute, getTableValueWithId} from '../../../factories';
import {screen, waitFor} from '@testing-library/react';
import userEvent from '@testing-library/user-event';

jest.mock('../../../../src/fetchers/RecordFetcher');

// TODO: use mockScroll()
type EntryCallback = (entries: {isIntersecting: boolean}[]) => void;

const intersectionObserverMock = (_callback: EntryCallback) => ({
  observe: jest.fn(() => null),
  unobserve: jest.fn(),
});
window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

const tableAttribute = getComplexTableAttribute('record');

const openDropDown = async () => {
  userEvent.click(await screen.findByTitle('pim_common.open'));
};

describe('RecordInput', () => {
  it('should render the component', async () => {
    const onChange = jest.fn();
    renderWithProviders(
      <RecordInput
        columnDefinition={tableAttribute.table_configuration[0]}
        highlighted={false}
        inError={false}
        row={getTableValueWithId('record')[0]}
        onChange={onChange}
        attribute={tableAttribute}
        setAttribute={jest.fn()}
      />
    );

    expect(await screen.findByTitle('Vannes')).toBeInTheDocument();
    const clearButton = screen.getByTitle('pim_common.clear');
    expect(clearButton).toBeInTheDocument();

    userEvent.click(clearButton);
    expect(onChange).toBeCalledWith(undefined);

    await openDropDown();
    expect(await screen.findByTitle('lannion00893335_2e73_41e3_ac34_763fb6a35107')).toBeInTheDocument();
    expect(screen.getByTitle('lannion.jpg')).toBeInTheDocument();
    expect(screen.getByText('Lannion')).toBeInTheDocument();
    expect(screen.getByText('75%')).toBeInTheDocument();
    expect(screen.getAllByTitle('akeneo_reference_entities_record_edit')[0]).toBeInTheDocument();

    userEvent.click(screen.getByTitle('lannion00893335_2e73_41e3_ac34_763fb6a35107'));
    expect(onChange).toBeCalledWith('lannion00893335_2e73_41e3_ac34_763fb6a35107');
  });

  it('should not render any value when cell it is undefined', () => {
    renderWithProviders(
      <RecordInput
        columnDefinition={tableAttribute.table_configuration[0]}
        highlighted={false}
        inError={false}
        row={getTableValueWithId('select')[0]}
        onChange={jest.fn()}
        attribute={tableAttribute}
        setAttribute={jest.fn()}
      />
    );

    expect(screen.queryByTitle('Vannes')).not.toBeInTheDocument();
  });

  it('should narrow record options on search', async () => {
    renderWithProviders(
      <RecordInput
        columnDefinition={tableAttribute.table_configuration[0]}
        highlighted={false}
        inError={false}
        row={getTableValueWithId('record')[0]}
        onChange={jest.fn()}
        attribute={tableAttribute}
        setAttribute={jest.fn()}
      />
    );

    await openDropDown();
    await userEvent.type(screen.getByTitle('pim_common.search'), 'Vannes');

    expect(await screen.findByTitle('vannes00bcf56a_2aa9_47c5_ac90_a973460b18a3')).toBeInTheDocument();
    await waitFor(() => {
      expect(screen.queryByTitle('lannion00893335_2e73_41e3_ac34_763fb6a35107')).not.toBeInTheDocument();
    });
  });

  it('should display default image when input has no file', async () => {
    renderWithProviders(
      <RecordInput
        columnDefinition={tableAttribute.table_configuration[0]}
        highlighted={false}
        inError={false}
        row={getTableValueWithId('record')[0]}
        onChange={jest.fn()}
        attribute={tableAttribute}
        setAttribute={jest.fn()}
      />
    );

    await openDropDown();
    expect(screen.getAllByTitle('default').length).toBe(2);
  });
});
