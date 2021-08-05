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
  createEvent,
  getByText,
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

const blackOption: AttributeOption = {
  id: 14,
  code: 'black',
  optionValues: {
    en_US: {id: 1, value: 'Black', locale: 'en_US'},
    fr_FR: {id: 2, value: 'Noir', locale: 'fr_FR'},
  },
};

const blueOption: AttributeOption = {
  id: 80,
  code: 'blue',
  optionValues: {
    en_US: {id: 3, value: 'Blue', locale: 'en_US'},
    fr_FR: {id: 4, value: 'Bleu', locale: 'fr_FR'},
  },
};

const options = [blueOption, blackOption];

describe('Attribute options table', () => {
  test('it renders an empty attribute options list', async () => {
    await renderComponent([], false, jest.fn(), jest.fn(), jest.fn());
    expect(getByRole(container, 'attribute-options-list')).toBeEmptyDOMElement();
  });

  test('it renders a list of 2 options not sorted alphabetically by default', async () => {
    global.fetch = jest.fn().mockImplementationOnce(route => {
      return {json: () => options};
    });

    await renderComponent(options, false, jest.fn(), jest.fn(), jest.fn());

    const attributeOptionsLabel = getAllByRole(container, 'attribute-option-item-label');
    const attributeOptionsCode = getAllByRole(container, 'attribute-option-item-code');
    expect(attributeOptionsLabel.length).toBe(2);
    expect(attributeOptionsCode.length).toBe(2);
    expect(attributeOptionsLabel[0].textContent).toBe('Blue');
    expect(attributeOptionsLabel[1].textContent).toBe('Black');
    expect(attributeOptionsCode[0].textContent).toBe('blue');
    expect(attributeOptionsCode[1].textContent).toBe('black');

    expect(queryByRole(container, 'new-option-placeholder')).toBeNull();
  });

  test('it renders a list of 2 options sorted alphabetically by default', async () => {
    await renderComponent(options, true, jest.fn(), jest.fn(), jest.fn());

    const attributeOptionsLabel = getAllByRole(container, 'attribute-option-item-label');
    const attributeOptionsCode = getAllByRole(container, 'attribute-option-item-code');
    expect(attributeOptionsLabel.length).toBe(2);
    expect(attributeOptionsCode.length).toBe(2);
    expect(attributeOptionsLabel[0].textContent).toBe('Black');
    expect(attributeOptionsLabel[1].textContent).toBe('Blue');
    expect(attributeOptionsCode[0].textContent).toBe('black');
    expect(attributeOptionsCode[1].textContent).toBe('blue');
  });

  test('the list order can be toggled', async () => {
    await renderComponent(options, false, jest.fn(), jest.fn(), jest.fn());

    let attributeOptionsLabel = getAllByRole(container, 'attribute-option-item-label');
    let attributeOptionsCode = getAllByRole(container, 'attribute-option-item-code');
    expect(attributeOptionsLabel.length).toBe(2);
    expect(attributeOptionsCode.length).toBe(2);
    expect(attributeOptionsLabel[0].textContent).toBe('Blue');
    expect(attributeOptionsLabel[1].textContent).toBe('Black');
    expect(attributeOptionsCode[0].textContent).toBe('blue');
    expect(attributeOptionsCode[1].textContent).toBe('black');

    const autoOptionSortYes = getByText(container, 'pim_common.yes');
    await fireEvent.click(autoOptionSortYes);

    attributeOptionsLabel = getAllByRole(container, 'attribute-option-item-label');
    attributeOptionsCode = getAllByRole(container, 'attribute-option-item-code');
    expect(attributeOptionsLabel.length).toBe(2);
    expect(attributeOptionsCode.length).toBe(2);
    expect(attributeOptionsLabel[0].textContent).toBe('Black');
    expect(attributeOptionsLabel[1].textContent).toBe('Blue');
    expect(attributeOptionsCode[0].textContent).toBe('black');
    expect(attributeOptionsCode[1].textContent).toBe('blue');

    const autoOptionSortNo = getByText(container, 'pim_common.no');
    await fireEvent.click(autoOptionSortNo);

    attributeOptionsLabel = getAllByRole(container, 'attribute-option-item-label');
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
    await renderComponent(options, false, jest.fn(), showNewOptionFormCallback, jest.fn());

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
    const blueOptionId = 80;
    await renderComponent(options, false, selectOptionCallback, jest.fn(), jest.fn(), blueOptionId);

    expect(queryByRole(container, 'new-option-placeholder')).toBeNull();

    const optionItems = queryAllByRole(container, 'attribute-option-item');
    const blueOption = optionItems[0];
    const blackOption = optionItems[1];
    expect(blueOption).toHaveAttribute('data-testid', 'is-selected');
    expect(blackOption).toHaveAttribute('data-testid', 'is-not-selected');

    const optionLabels = queryAllByRole(container, 'attribute-option-item-label');
    const blackOptionLabel = optionLabels[1];
    await fireEvent.click(blackOptionLabel);

    const blackOptionId = 14;
    expect(selectOptionCallback).toHaveBeenNthCalledWith(1, blackOptionId);
  });

  test('it allows attribute option to be deleted', async () => {
    const deleteAttributeOptionCallback = jest.fn();
    await renderComponent(options, false, jest.fn(), deleteAttributeOptionCallback, jest.fn());

    const optionItems = queryAllByRole(container, 'attribute-option-item');
    const blueOption = optionItems[0];

    const deleteButton = getByRole(blueOption, 'attribute-option-delete-button');
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
    await renderComponent(options, false, jest.fn(), deleteAttributeOptionCallback, jest.fn());

    const optionItems = queryAllByRole(container, 'attribute-option-item');
    const blueOption = optionItems[0];

    const deleteButton = getByRole(blueOption, 'attribute-option-delete-button');
    await fireEvent.click(deleteButton);
    const deleteConfirmationModal = queryByRole(blueOption, 'attribute-option-delete-confirmation-modal');
    let cancelButtons = queryAllByRole(blueOption, 'attribute-option-confirm-cancel-button');

    expect(cancelButtons).toHaveLength(2);

    fireEvent.click(cancelButtons[0]);
    await expect(deleteAttributeOptionCallback).not.toHaveBeenCalled();
    expect(deleteConfirmationModal).not.toBeInTheDocument();

    await fireEvent.click(deleteButton);
    cancelButtons = queryAllByRole(blueOption, 'attribute-option-confirm-cancel-button');
    await fireEvent.click(cancelButtons[1]);
    expect(deleteAttributeOptionCallback).not.toHaveBeenCalled();
    expect(deleteConfirmationModal).not.toBeInTheDocument();
  });

  /*test('an attribute option will not be moved upwards if the treshold is not met', async () => {
    const moveOptionCallback = jest.fn();
    await renderComponent(
      blueOption,
      jest.fn(),
      jest.fn(),
      moveOptionCallback,
      jest.fn(),
      false,
      {code: 'black', index: 0},
      jest.fn()
    );
    const attributeOptionMoveHandle = getByRole(container, 'attribute-option-move-handle');

    fireEvent.dragOver(attributeOptionMoveHandle, {
      target: {
        getBoundingClientRect: jest.fn().mockImplementation(() => {
          return {bottom: 10, top: 0};
        }),
        clientY: 4,
      },
    });

    expect(moveOptionCallback).not.toHaveBeenCalled();
  });

  test('an attribute option will not be moved downwards if the treshold is not met', async () => {
    const moveOptionCallback = jest.fn();
    await renderComponent(
      blueOption,
      jest.fn(),
      jest.fn(),
      moveOptionCallback,
      jest.fn(),
      false,
      {code: 'black', index: 2},
      jest.fn()
    );
    const attributeOptionMoveHandle = getByRole(container, 'attribute-option-move-handle');

    fireEvent.dragOver(attributeOptionMoveHandle, {
      target: {
        getBoundingClientRect: jest.fn().mockImplementation(() => {
          return {bottom: 10, top: 0};
        }),
        clientY: 6,
      },
    });

    expect(moveOptionCallback).not.toHaveBeenCalled();
  });

  test('an attribute option cannot replace itself', async () => {
    const moveOptionCallback = jest.fn();
    await renderComponent(
      blackOption,
      jest.fn(),
      jest.fn(),
      moveOptionCallback,
      jest.fn(),
      false,
      {code: 'black', index: 2},
      jest.fn()
    );
    const attributeOptionMoveHandle = getByRole(container, 'attribute-option-move-handle');

    fireEvent.dragOver(attributeOptionMoveHandle);

    expect(moveOptionCallback).not.toHaveBeenCalled();
  });

  test('an attribute option cannot be moved if the options are sorted alphabetically', async () => {
    const setDragItem = jest.fn();
    await renderComponent(blackOption, jest.fn(), jest.fn(), jest.fn(), jest.fn(), true, setDragItem);
    const attributeOptionMoveHandle = getByRole(container, 'attribute-option-move-handle');

    fireEvent.dragStart(attributeOptionMoveHandle);
    expect(setDragItem).not.toHaveBeenCalled();

    fireEvent.dragEnd(attributeOptionMoveHandle);
    expect(setDragItem).not.toHaveBeenCalled();
  });*/
});

async function moveBlueOptionToBackOption(attributeOptionMoveHandle, attributeOptions) {
  const dragStartEvent = createEvent.dragStart(attributeOptionMoveHandle);
  const setDragImage = jest.fn();
  Object.assign(dragStartEvent, {
    dataTransfer: {
      setDragImage,
    },
  });
  await fireEvent(attributeOptionMoveHandle, dragStartEvent);
  await fireEvent.dragOver(attributeOptions[1], {
    target: {
      getBoundingClientRect: jest.fn().mockImplementation(() => {
        return {bottom: 10, top: 0};
      }),
      clientY: 6,
    },
  });
  await fireEvent.drop(attributeOptionMoveHandle);
  await fireEvent.dragEnd(attributeOptionMoveHandle);
}

async function renderComponent(
  options: any,
  autoSortOptions: any,
  selectOptionCallback: jest.Mock<any, any>,
  showNewOptionFormCallback: jest.Mock<any, any>,
  manuallySortAttributeOptionsCallback: jest.Mock<any, any>,
  selectedOptionId = null
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
                deleteAttributeOption={jest.fn()}
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
