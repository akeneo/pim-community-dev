import React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {
  act,
  fireEvent,
  getByText,
  getByTitle,
  getAllByTestId,
  getByTestId,
  queryByTestId,
  queryAllByTestId,
} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import AttributeOptionTable from 'akeneopimstructure/js/attribute-option/components/AttributeOptionTable';
import {
  AttributeContextProvider,
  AttributeOptionsContextProvider,
  LocalesContextProvider,
} from 'akeneopimstructure/js/attribute-option/contexts';
import OverridePimStyle from 'akeneopimstructure/js/attribute-option/components/OverridePimStyles';
import baseFetcher from 'akeneopimstructure/js/attribute-option/fetchers/baseFetcher';

jest.mock('akeneopimstructure/js/attribute-option/fetchers/baseFetcher');

let container: HTMLElement;

beforeEach(() => {
  jest.clearAllMocks();
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
});

afterAll(() => {
  jest.restoreAllMocks();
});

const blueOption: AttributeOption = {
  id: 14,
  code: 'blue',
  optionValues: {
    en_US: {id: 1, value: 'Blue', locale: 'en_US'},
    fr_FR: {id: 2, value: 'Bleu', locale: 'fr_FR'},
  },
  toImprove: undefined,
};

const blackOption: AttributeOption = {
  id: 80,
  code: 'black',
  optionValues: {
    en_US: {id: 3, value: 'Black', locale: 'en_US'},
    fr_FR: {id: 4, value: 'Noir', locale: 'fr_FR'},
    de_DE: {id: 5, value: 'Schwarz', locale: 'fr_FR'},
  },
  toImprove: undefined,
};

const redOption: AttributeOption = {
  id: 1,
  code: 'red',
  optionValues: {
    en_US: {id: 1, value: 'Red', locale: 'en_US'},
    fr_FR: {id: 2, value: 'Rouge', locale: 'fr_FR'},
  },
  toImprove: undefined,
};

const options = [blueOption, blackOption, redOption];

describe('Attribute options table', () => {
  test('it renders an empty attribute options list', async () => {
    await renderComponent([], false, jest.fn(), jest.fn(), jest.fn(), jest.fn(), null);

    expect(getByTestId(container, 'attribute-options-list')).toBeEmptyDOMElement();
  });

  test('it renders a list of 2 options not sorted alphabetically by default', async () => {
    await renderComponent(options, false, jest.fn(), jest.fn(), jest.fn(), jest.fn(), null);

    const attributeOptionsLabel = getAllByTestId(container, 'attribute-option-item-label');
    const attributeOptionsCode = getAllByTestId(container, 'attribute-option-item-code');
    expect(attributeOptionsLabel.length).toBe(3);
    expect(attributeOptionsCode.length).toBe(3);
    expect(attributeOptionsLabel[0].textContent).toBe('Blue');
    expect(attributeOptionsLabel[1].textContent).toBe('Black');
    expect(attributeOptionsCode[0].textContent).toBe('blue');
    expect(attributeOptionsCode[1].textContent).toBe('black');

    expect(queryByTestId(container, 'new-option-placeholder')).toBeNull();
  });

  test('it renders a list of 2 options sorted alphabetically by default', async () => {
    await renderComponent(options, true, jest.fn(), jest.fn(), jest.fn(), jest.fn(), null);

    const attributeOptionsLabel = getAllByTestId(container, 'attribute-option-item-label');
    const attributeOptionsCode = getAllByTestId(container, 'attribute-option-item-code');
    expect(attributeOptionsLabel.length).toBe(3);
    expect(attributeOptionsCode.length).toBe(3);
    expect(attributeOptionsLabel[0].textContent).toBe('Black');
    expect(attributeOptionsLabel[1].textContent).toBe('Blue');
    expect(attributeOptionsCode[0].textContent).toBe('black');
    expect(attributeOptionsCode[1].textContent).toBe('blue');
  });

  test('the list order can be toggled', async () => {
    await renderComponent(options, false, jest.fn(), jest.fn(), jest.fn(), jest.fn(), null);

    let attributeOptionsLabel = getAllByTestId(container, 'attribute-option-item-label');
    let attributeOptionsCode = getAllByTestId(container, 'attribute-option-item-code');
    expect(attributeOptionsLabel.length).toBe(3);
    expect(attributeOptionsCode.length).toBe(3);
    expect(attributeOptionsLabel[0].textContent).toBe('Blue');
    expect(attributeOptionsLabel[1].textContent).toBe('Black');
    expect(attributeOptionsCode[0].textContent).toBe('blue');
    expect(attributeOptionsCode[1].textContent).toBe('black');

    const autoOptionSortYes = getByText(container, 'pim_common.yes');
    await fireEvent.click(autoOptionSortYes);

    attributeOptionsLabel = getAllByTestId(container, 'attribute-option-item-label');
    attributeOptionsCode = getAllByTestId(container, 'attribute-option-item-code');
    expect(attributeOptionsLabel.length).toBe(3);
    expect(attributeOptionsCode.length).toBe(3);
    expect(attributeOptionsLabel[0].textContent).toBe('Black');
    expect(attributeOptionsLabel[1].textContent).toBe('Blue');
    expect(attributeOptionsCode[0].textContent).toBe('black');
    expect(attributeOptionsCode[1].textContent).toBe('blue');

    const autoOptionSortNo = getByText(container, 'pim_common.no');
    await fireEvent.click(autoOptionSortNo);

    attributeOptionsLabel = getAllByTestId(container, 'attribute-option-item-label');
    attributeOptionsCode = getAllByTestId(container, 'attribute-option-item-code');
    expect(attributeOptionsLabel.length).toBe(3);
    expect(attributeOptionsCode.length).toBe(3);
    expect(attributeOptionsLabel[0].textContent).toBe('Blue');
    expect(attributeOptionsLabel[1].textContent).toBe('Black');
    expect(attributeOptionsCode[0].textContent).toBe('blue');
    expect(attributeOptionsCode[1].textContent).toBe('black');
  });

  test('it displays a fake new option at the end when adding a new option', async () => {
    window.HTMLElement.prototype.scrollIntoView = jest.fn();
    const showNewOptionFormCallback = jest.fn();
    await renderComponent(options, false, jest.fn(), showNewOptionFormCallback, jest.fn(), jest.fn(), null);

    expect(queryByTestId(container, 'new-option-placeholder')).toBeNull();

    const addNewOptionButton = getByTestId(container, 'add-new-attribute-option-button');
    await fireEvent.click(addNewOptionButton);
    const newOptionPlaceholder = getByTestId(container, 'new-option-placeholder');

    expect(newOptionPlaceholder).toBeInTheDocument();
    expect(showNewOptionFormCallback).toHaveBeenNthCalledWith(1, true);

    const cancelNewOptionButton = getByTestId(container, 'new-option-cancel');
    fireEvent.click(cancelNewOptionButton);
    expect(newOptionPlaceholder).not.toBeInTheDocument();
  });

  test('it allows option selection', async () => {
    const selectOptionCallback = jest.fn();
    const blueOptionId = 14;
    await renderComponent(options, false, selectOptionCallback, jest.fn(), jest.fn(), jest.fn(), blueOptionId);

    expect(queryByTestId(container, 'new-option-placeholder')).toBeNull();

    const optionItems = getAllByTestId(container, 'attribute-option-item');
    const blueOption = optionItems[0];
    const blackOption = optionItems[1];
    expect(blueOption).toHaveAttribute('data-is-selected', 'true');
    expect(blackOption).toHaveAttribute('data-is-selected', 'false');

    const optionLabels = getAllByTestId(container, 'attribute-option-item-label');
    const blackOptionLabel = optionLabels[1];
    await fireEvent.click(blackOptionLabel);

    const blackOptionId = 80;
    expect(selectOptionCallback).toHaveBeenNthCalledWith(1, blackOptionId);
  });

  test('it allows attribute option to be deleted', async () => {
    const deleteAttributeOptionCallback = jest.fn();
    await renderComponent(options, false, jest.fn(), jest.fn(), jest.fn(), deleteAttributeOptionCallback, null);

    const optionItems = getAllByTestId(container, 'attribute-option-item');
    const blueOption = optionItems[0];

    const deleteButton = getByTestId(blueOption, 'attribute-option-delete-button');
    let deleteConfirmationModal = queryByTestId(container, 'attribute-option-delete-confirmation-modal');
    expect(deleteConfirmationModal).not.toBeInTheDocument();

    await fireEvent.click(deleteButton);

    deleteConfirmationModal = getByTestId(container, 'attribute-option-delete-confirmation-modal');
    const confirmDeleteButton = getByTestId(container, 'attribute-option-confirm-delete-button');
    const cancelButtons = queryAllByTestId(container, 'attribute-option-confirm-cancel-button');

    expect(deleteAttributeOptionCallback).not.toHaveBeenCalled();
    expect(deleteConfirmationModal).toBeInTheDocument();
    expect(cancelButtons).toHaveLength(2);

    await fireEvent.click(confirmDeleteButton);
    expect(deleteAttributeOptionCallback).toHaveBeenCalled();
  });

  test('the attribute option deletion can be cancelled with the 2 cancel buttons', async () => {
    const deleteAttributeOptionCallback = jest.fn();
    await renderComponent(options, false, jest.fn(), jest.fn(), jest.fn(), deleteAttributeOptionCallback, null);

    const optionItems = getAllByTestId(container, 'attribute-option-item');
    const blueOption = optionItems[0];

    const deleteButton = getByTestId(blueOption, 'attribute-option-delete-button');
    await fireEvent.click(deleteButton);
    const deleteConfirmationModal = queryByTestId(blueOption, 'attribute-option-delete-confirmation-modal');
    let cancelButtons = queryAllByTestId(container, 'attribute-option-confirm-cancel-button');

    expect(cancelButtons).toHaveLength(2);

    await fireEvent.click(cancelButtons[0]);
    expect(deleteAttributeOptionCallback).not.toHaveBeenCalled();
    expect(deleteConfirmationModal).not.toBeInTheDocument();

    await fireEvent.click(deleteButton);
    cancelButtons = queryAllByTestId(container, 'attribute-option-confirm-cancel-button');
    await fireEvent.click(cancelButtons[1]);
    expect(deleteAttributeOptionCallback).not.toHaveBeenCalled();
    expect(deleteConfirmationModal).not.toBeInTheDocument();
  });

  test('the attribute can be dragged and dropped', async () => {
    const manuallySortAttributeOptionsCallback = jest.fn();
    await renderComponent(options, false, jest.fn(), jest.fn(), manuallySortAttributeOptionsCallback, jest.fn(), null);

    const optionItems = getAllByTestId(container, 'attribute-option-item');
    const blueOption = optionItems[0];
    const blackOption = optionItems[1];
    const redOption = optionItems[2];

    let dataTransferred = '';
    const dataTransfer = {
      getData: (_format: string) => {
        return dataTransferred;
      },
      setData: (_format: string, data: string) => {
        dataTransferred = data;
      },
    };

    await fireEvent.dragStart(blueOption, {dataTransfer});
    await fireEvent.dragEnter(blackOption, {dataTransfer});
    await fireEvent.dragOver(blackOption, {dataTransfer});
    await fireEvent.drop(blackOption, {dataTransfer});

    let attributeOptionsLabel = getAllByTestId(container, 'attribute-option-item-label');
    let attributeOptionsCode = getAllByTestId(container, 'attribute-option-item-code');
    expect(attributeOptionsLabel.length).toBe(3);
    expect(attributeOptionsCode.length).toBe(3);
    expect(attributeOptionsLabel[0].textContent).toBe('Black');
    expect(attributeOptionsLabel[1].textContent).toBe('Blue');
    expect(attributeOptionsLabel[2].textContent).toBe('Red');
    expect(attributeOptionsCode[0].textContent).toBe('black');
    expect(attributeOptionsCode[1].textContent).toBe('blue');
    expect(attributeOptionsCode[2].textContent).toBe('red');

    expect(manuallySortAttributeOptionsCallback).toHaveBeenCalled();

    // Move red to the 1st position
    fireEvent.dragStart(redOption, {dataTransfer});
    fireEvent.dragEnter(blackOption, {dataTransfer});
    fireEvent.drop(blackOption, {dataTransfer});
    fireEvent.dragLeave(blackOption, {dataTransfer});
    fireEvent.dragEnd(redOption, {dataTransfer});

    attributeOptionsLabel = getAllByTestId(container, 'attribute-option-item-label');

    expect(attributeOptionsLabel[0].textContent).toBe('Red');
    expect(attributeOptionsLabel[1].textContent).toBe('Black');
    expect(attributeOptionsLabel[2].textContent).toBe('Blue');
  });

  test('an attribute option cannot be moved if the options are sorted alphabetically', async () => {
    const manuallySortAttributeOptionsCallback = jest.fn();
    await renderComponent(options, true, jest.fn(), jest.fn(), manuallySortAttributeOptionsCallback, jest.fn(), null);

    const optionItems = getAllByTestId(container, 'attribute-option-item');
    const blueOption = optionItems[0];
    const blackOption = optionItems[1];

    let dataTransferred = '';
    const dataTransfer = {
      getData: (_format: string) => {
        return dataTransferred;
      },
      setData: (_format: string, data: string) => {
        dataTransferred = data;
      },
    };

    await fireEvent.dragStart(blueOption, {dataTransfer});
    await fireEvent.dragEnter(blackOption, {dataTransfer});
    await fireEvent.dragOver(blackOption, {dataTransfer});
    await fireEvent.drop(blackOption, {dataTransfer});

    expect(manuallySortAttributeOptionsCallback).not.toHaveBeenCalled();
  });

  test('it finds item element in the list after a search', async () => {
    jest.useFakeTimers();

    await renderComponent(options, false, jest.fn(), jest.fn(), jest.fn(), jest.fn(), null);

    const searchInput = getByTitle(container, 'pim_common.search');
    fireEvent.change(searchInput, {target: {value: 'Blue'}});

    await act(async () => {
      setTimeout(() => {
        const optionItems = getAllByTestId(container, 'attribute-option-item');
        const attributeOptionsLabel = getAllByTestId(container, 'attribute-option-item-label');
        const attributeOptionsCode = getAllByTestId(container, 'attribute-option-item-code');

        expect(optionItems.length).toBe(1);
        expect(attributeOptionsLabel[0].textContent).toBe('Blue');
        expect(attributeOptionsCode[0].textContent).toBe('blue');
      }, 300);

      jest.runAllTimers();
    });
  });

  test('it does not find any item in the list after a search', async () => {
    jest.useFakeTimers();

    await renderComponent(options, false, jest.fn(), jest.fn(), jest.fn(), jest.fn(), null);

    const searchInput = getByTitle(container, 'pim_common.search');
    fireEvent.change(searchInput, {target: {value: 'Z'}});

    await act(async () => {
      setTimeout(() => {
        const noResultElement = getByText(
          container,
          'pim_enrich.entity.attribute_option.module.edit.search.no_result.title'
        );
        expect(noResultElement).toBeInTheDocument();
      }, 300);

      jest.runAllTimers();
    });
  });
});

async function renderComponent(
  options: any,
  autoSortOptions: any,
  selectOptionCallback: jest.Mock<any, any>,
  showNewOptionFormCallback: jest.Mock<any, any>,
  manuallySortAttributeOptionsCallback: jest.Mock<any, any>,
  deleteAttributeOptionCallback: jest.Mock<any, any>,
  selectedOptionId: number | null
) {
  baseFetcher.mockResolvedValueOnce(options);

  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AttributeContextProvider attributeId={8} autoSortOptions={autoSortOptions}>
            <LocalesContextProvider>
              <AttributeOptionsContextProvider>
                <OverridePimStyle />
                <AttributeOptionTable
                  selectAttributeOption={selectOptionCallback}
                  selectedOptionId={selectedOptionId}
                  isNewOptionFormDisplayed={false}
                  showNewOptionForm={showNewOptionFormCallback}
                  deleteAttributeOption={deleteAttributeOptionCallback}
                  manuallySortAttributeOptions={manuallySortAttributeOptionsCallback}
                />
              </AttributeOptionsContextProvider>
            </LocalesContextProvider>
          </AttributeContextProvider>
        </ThemeProvider>
      </DependenciesProvider>,
      container
    );
  });
}
