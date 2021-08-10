import React from 'react';
import * as ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import '@testing-library/jest-dom/extend-expect';
import {
  act,
  fireEvent,
  getAllByRole,
  getByRole,
  queryByRole,
  queryAllByRole,
  getByText,
  getByTitle,
  getAllByTestId,
  getByTestId,
} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {createStoreWithInitialState} from 'akeneopimstructure/js/attribute-option/store/store';
import {AttributeContextProvider} from 'akeneopimstructure/js/attribute-option/contexts';
import {AttributeOption} from '../../../../../../src/Akeneo/Pim/Structure/Bundle/Resources/public/js/attribute-option/model';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import AttributeOptionTable from '../../../../../../src/Akeneo/Pim/Structure/Bundle/Resources/public/js/attribute-option/components/AttributeOptionTable';

let container: HTMLElement;

beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
});

const blueOption: AttributeOption = {
  id: 14,
  code: 'blue',
  optionValues: {
    en_US: {id: 1, value: 'Blue', locale: 'en_US'},
    fr_FR: {id: 2, value: 'Bleu', locale: 'fr_FR'},
  },
};

const blackOption: AttributeOption = {
  id: 80,
  code: 'black',
  optionValues: {
    en_US: {id: 3, value: 'Black', locale: 'en_US'},
    fr_FR: {id: 4, value: 'Noir', locale: 'fr_FR'},
  },
};

const options = [blueOption, blackOption];

describe('Attribute options table', () => {
  test('it renders an empty attribute options list', async () => {
    await renderComponent([], false, jest.fn(), jest.fn(), jest.fn(), jest.fn(), null);

    expect(getByRole(container, 'attribute-options-list')).toBeEmptyDOMElement();
  });

  test('it renders a list of 2 options not sorted alphabetically by default', async () => {
    await renderComponent(options, false, jest.fn(), jest.fn(), jest.fn(), jest.fn(), null);

    const attributeOptionsLabel = getAllByTestId(container, 'attribute-option-item-label');
    const attributeOptionsCode = getAllByTestId(container, 'attribute-option-item-code');
    expect(attributeOptionsLabel.length).toBe(2);
    expect(attributeOptionsCode.length).toBe(2);
    expect(attributeOptionsLabel[0].textContent).toBe('Blue');
    expect(attributeOptionsLabel[1].textContent).toBe('Black');
    expect(attributeOptionsCode[0].textContent).toBe('blue');
    expect(attributeOptionsCode[1].textContent).toBe('black');

    expect(queryByRole(container, 'new-option-placeholder')).toBeNull();
  });

  test('it renders a list of 2 options sorted alphabetically by default', async () => {
    await renderComponent(options, true, jest.fn(), jest.fn(), jest.fn(), jest.fn(), null);

    const attributeOptionsLabel = getAllByTestId(container, 'attribute-option-item-label');
    const attributeOptionsCode = getAllByRole(container, 'attribute-option-item-code');
    expect(attributeOptionsLabel.length).toBe(2);
    expect(attributeOptionsCode.length).toBe(2);
    expect(attributeOptionsLabel[0].textContent).toBe('Black');
    expect(attributeOptionsLabel[1].textContent).toBe('Blue');
    expect(attributeOptionsCode[0].textContent).toBe('black');
    expect(attributeOptionsCode[1].textContent).toBe('blue');
  });

  test('the list order can be toggled', async () => {
    await renderComponent(options, false, jest.fn(), jest.fn(), jest.fn(), jest.fn(), null);

    let attributeOptionsLabel = getAllByTestId(container, 'attribute-option-item-label');
    let attributeOptionsCode = getAllByRole(container, 'attribute-option-item-code');
    expect(attributeOptionsLabel.length).toBe(2);
    expect(attributeOptionsCode.length).toBe(2);
    expect(attributeOptionsLabel[0].textContent).toBe('Blue');
    expect(attributeOptionsLabel[1].textContent).toBe('Black');
    expect(attributeOptionsCode[0].textContent).toBe('blue');
    expect(attributeOptionsCode[1].textContent).toBe('black');

    const autoOptionSortYes = getByText(container, 'pim_common.yes');
    await fireEvent.click(autoOptionSortYes);

    attributeOptionsLabel = getAllByTestId(container, 'attribute-option-item-label');
    attributeOptionsCode = getAllByRole(container, 'attribute-option-item-code');
    expect(attributeOptionsLabel.length).toBe(2);
    expect(attributeOptionsCode.length).toBe(2);
    expect(attributeOptionsLabel[0].textContent).toBe('Black');
    expect(attributeOptionsLabel[1].textContent).toBe('Blue');
    expect(attributeOptionsCode[0].textContent).toBe('black');
    expect(attributeOptionsCode[1].textContent).toBe('blue');

    const autoOptionSortNo = getByText(container, 'pim_common.no');
    await fireEvent.click(autoOptionSortNo);

    attributeOptionsLabel = getAllByTestId(container, 'attribute-option-item-label');
    attributeOptionsCode = getAllByRole(container, 'attribute-option-item-code');
    expect(attributeOptionsLabel.length).toBe(2);
    expect(attributeOptionsCode.length).toBe(2);
    expect(attributeOptionsLabel[0].textContent).toBe('Blue');
    expect(attributeOptionsLabel[1].textContent).toBe('Black');
    expect(attributeOptionsCode[0].textContent).toBe('blue');
    expect(attributeOptionsCode[1].textContent).toBe('black');
  });

  test('it displays a fake new option at the end when adding a new option', async () => {
    window.HTMLElement.prototype.scrollIntoView = jest.fn();
    const showNewOptionFormCallback = jest.fn();
    await renderComponent(options, false, jest.fn(), showNewOptionFormCallback, jest.fn(), jest.fn(), null);

    expect(queryByRole(container, 'new-option-placeholder')).toBeNull();

    const addNewOptionButton = getByRole(container, 'add-new-attribute-option-button');
    await fireEvent.click(addNewOptionButton);
    const newOptionPlaceholder = getByRole(container, 'new-option-placeholder');

    expect(newOptionPlaceholder).toBeInTheDocument();
    expect(showNewOptionFormCallback).toHaveBeenNthCalledWith(1, true);

    const cancelNewOptionButton = getByRole(container, 'new-option-cancel');
    fireEvent.click(cancelNewOptionButton);
    expect(newOptionPlaceholder).not.toBeInTheDocument();
  });

  test('it allows option selection', async () => {
    const selectOptionCallback = jest.fn();
    const blueOptionId = 14;
    await renderComponent(options, false, selectOptionCallback, jest.fn(), jest.fn(), jest.fn(), blueOptionId);

    expect(queryByRole(container, 'new-option-placeholder')).toBeNull();

    const optionItems = queryAllByRole(container, 'attribute-option-item');
    const blueOption = optionItems[0];
    const blackOption = optionItems[1];
    expect(blueOption).toHaveAttribute('data-testid', 'is-selected');
    expect(blackOption).toHaveAttribute('data-testid', 'is-not-selected');

    const optionLabels = getAllByTestId(container, 'attribute-option-item-label');
    const blackOptionLabel = optionLabels[1];
    await fireEvent.click(blackOptionLabel);

    const blackOptionId = 80;
    expect(selectOptionCallback).toHaveBeenNthCalledWith(1, blackOptionId);
  });

  test('it allows attribute option to be deleted', async () => {
    const deleteAttributeOptionCallback = jest.fn();
    await renderComponent(options, false, jest.fn(), jest.fn(), jest.fn(), deleteAttributeOptionCallback, null);

    const optionItems = queryAllByRole(container, 'attribute-option-item');
    const blueOption = optionItems[0];

    const deleteButton = getByTestId(blueOption, 'attribute-option-delete-button');
    let deleteConfirmationModal = queryByRole(blueOption, 'attribute-option-delete-confirmation-modal');
    expect(deleteConfirmationModal).not.toBeInTheDocument();

    await fireEvent.click(deleteButton);

    deleteConfirmationModal = getByRole(blueOption, 'attribute-option-delete-confirmation-modal');
    const confirmDeleteButton = getByRole(blueOption, 'attribute-option-confirm-delete-button');
    const cancelButtons = queryAllByRole(blueOption, 'attribute-option-confirm-cancel-button');

    expect(deleteAttributeOptionCallback).not.toHaveBeenCalled();
    expect(deleteConfirmationModal).toBeInTheDocument();
    expect(cancelButtons).toHaveLength(2);

    await fireEvent.click(confirmDeleteButton);
    expect(deleteAttributeOptionCallback).toHaveBeenCalled();
  });

  test('the attribute option deletion can be cancelled with the 2 cancel buttons', async () => {
    const deleteAttributeOptionCallback = jest.fn();
    await renderComponent(options, false, jest.fn(), jest.fn(), jest.fn(), deleteAttributeOptionCallback, null);

    const optionItems = queryAllByRole(container, 'attribute-option-item');
    const blueOption = optionItems[0];

    const deleteButton = getByRole(blueOption, 'attribute-option-delete-button');
    await fireEvent.click(deleteButton);
    const deleteConfirmationModal = queryByRole(blueOption, 'attribute-option-delete-confirmation-modal');
    let cancelButtons = queryAllByRole(blueOption, 'attribute-option-confirm-cancel-button');

    expect(cancelButtons).toHaveLength(2);

    await fireEvent.click(cancelButtons[0]);
    expect(deleteAttributeOptionCallback).not.toHaveBeenCalled();
    expect(deleteConfirmationModal).not.toBeInTheDocument();

    await fireEvent.click(deleteButton);
    cancelButtons = queryAllByRole(blueOption, 'attribute-option-confirm-cancel-button');
    await fireEvent.click(cancelButtons[1]);
    expect(deleteAttributeOptionCallback).not.toHaveBeenCalled();
    expect(deleteConfirmationModal).not.toBeInTheDocument();
  });

  test('the attribute can be dragged and dropped', async () => {
    const manuallySortAttributeOptionsCallback = jest.fn();
    await renderComponent(options, false, jest.fn(), jest.fn(), manuallySortAttributeOptionsCallback, jest.fn(), null);

    const optionItems = queryAllByRole(container, 'attribute-option-item');
    const blueOption = optionItems[0];
    const blackOption = optionItems[1];

    await fireEvent.dragStart(blueOption);
    await fireEvent.dragEnter(blackOption);
    await fireEvent.dragOver(blackOption);
    await fireEvent.drop(blackOption);

    let attributeOptionsLabel = getAllByTestId(container, 'attribute-option-item-label');
    let attributeOptionsCode = getAllByRole(container, 'attribute-option-item-code');
    expect(attributeOptionsLabel.length).toBe(2);
    expect(attributeOptionsCode.length).toBe(2);
    expect(attributeOptionsLabel[0].textContent).toBe('Black');
    expect(attributeOptionsLabel[1].textContent).toBe('Blue');
    expect(attributeOptionsCode[0].textContent).toBe('black');
    expect(attributeOptionsCode[1].textContent).toBe('blue');

    expect(manuallySortAttributeOptionsCallback).toHaveBeenCalled();
  });

  test('an attribute option cannot be moved if the options are sorted alphabetically', async () => {
    const manuallySortAttributeOptionsCallback = jest.fn();
    await renderComponent(options, true, jest.fn(), jest.fn(), manuallySortAttributeOptionsCallback, jest.fn(), null);

    const optionItems = queryAllByRole(container, 'attribute-option-item');
    const blueOption = optionItems[0];
    const blackOption = optionItems[1];

    await fireEvent.dragStart(blueOption);
    await fireEvent.dragEnter(blackOption);
    await fireEvent.dragOver(blackOption);
    await fireEvent.drop(blackOption);

    expect(manuallySortAttributeOptionsCallback).not.toHaveBeenCalled();
  });

  test('it finds item element in the list after a search', async () => {
    jest.useFakeTimers();

    await renderComponent(options, false, jest.fn(), jest.fn(), jest.fn(), jest.fn(), null);

    const searchInput = getByTitle(container, 'pim_common.search');
    fireEvent.change(searchInput, {target: {value: 'Blue'}});

    await act(async () => {
      setTimeout(() => {
        const optionItems = queryAllByRole(container, 'attribute-option-item');
        const attributeOptionsLabel = getAllByTestId(container, 'attribute-option-item-label');
        const attributeOptionsCode = getAllByRole(container, 'attribute-option-item-code');

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
  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <Provider store={createStoreWithInitialState({attributeOptions: options})}>
            <AttributeContextProvider attributeId={8} autoSortOptions={autoSortOptions}>
              <AttributeOptionTable
                selectAttributeOption={selectOptionCallback}
                isNewOptionFormDisplayed={false}
                showNewOptionForm={showNewOptionFormCallback}
                selectedOptionId={selectedOptionId}
                deleteAttributeOption={deleteAttributeOptionCallback}
                manuallySortAttributeOptions={manuallySortAttributeOptionsCallback}
              />
            </AttributeContextProvider>
          </Provider>
        </ThemeProvider>
      </DependenciesProvider>,
      container
    );
  });
}
