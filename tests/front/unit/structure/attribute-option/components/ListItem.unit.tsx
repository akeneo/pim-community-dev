import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, render} from '@testing-library/react';
import ListItem from 'akeneopimstructure/js/attribute-option/components/ListItem';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {
    AttributeContextProvider,
    AttributeOptionsContextProvider
} from 'akeneopimstructure/js/attribute-option/contexts';
import {Provider} from "react-redux";
import {createStoreWithInitialState} from "akeneopimstructure/js/attribute-option/store/store";


const blackOption: AttributeOption = {
    id: 80,
    code: 'black',
    optionValues: {
        en_US: {
            id: 1,
            value: 'Black',
            locale: 'en_US',
        },
    },
};

const blueOption: AttributeOption = {
    id: 14,
    code: 'blue',
    optionValues: {
        en_US: {
            id: 3,
            value: 'Blue',
            locale: 'en_US',
        },
    },
};

describe('Attribute options list item', () => {
    beforeEach(() => {
        jest.clearAllMocks();
    });

    afterAll(() => {
        jest.resetAllMocks();
    });

    test('it renders a list item', async () => {
        const onSelectCallback = jest.fn();

        const {getByRole} = renderComponent(
            blackOption,
            onSelectCallback,
            jest.fn(),
            jest.fn(),
            false,
            null,
            jest.fn()
        );

        const attributeOption = getByRole('attribute-option-item');
        expect(attributeOption).toHaveClass('AknAttributeOption-listItem--selected');

        const attributeOptionLabel = getByRole('attribute-option-item-label');
        act(() => {
            fireEvent.click(attributeOptionLabel);
        });
        expect(onSelectCallback).toHaveBeenNthCalledWith(1, 80);
    });

    test('it allows attribute option to be deleted', async () => {
        const {getByRole, queryByRole, queryAllByRole} = renderComponent(
            blackOption,
            jest.fn(),
            jest.fn(),
            jest.fn(),
            false,
            null,
            jest.fn()
        );

        const deleteButton = getByRole('attribute-option-delete-button');
        let deleteConfirmationModal = queryByRole('attribute-option-delete-confirmation-modal');
        expect(deleteConfirmationModal).not.toBeInTheDocument();

        act(() => {
            fireEvent.click(deleteButton);
        });

        deleteConfirmationModal = getByRole('attribute-option-delete-confirmation-modal');
        const confirmDeleteButton = getByRole('attribute-option-confirm-delete-button');
        const cancelButtons = queryAllByRole('attribute-option-confirm-cancel-button');

        expect(deleteConfirmationModal).toBeInTheDocument();
        expect(confirmDeleteButton).toBeInTheDocument();
        expect(cancelButtons).toHaveLength(2);

        act(() => {
            fireEvent.click(confirmDeleteButton);
        });

        expect(deleteConfirmationModal).not.toBeInTheDocument();
    });

    test('the attribute option deletion can be cancelled with the 2 cancel buttons', async () => {
        const {getByRole, queryAllByRole} = renderComponent(
            blackOption,
            jest.fn(),
            jest.fn(),
            jest.fn(),
            false,
            null,
            jest.fn()
        );

        const deleteButton = getByRole('attribute-option-delete-button');
        act(() => {
            fireEvent.click(deleteButton);
        });
        const deleteConfirmationModal = getByRole('attribute-option-delete-confirmation-modal');
        let cancelButtons = queryAllByRole('attribute-option-confirm-cancel-button');

        expect(cancelButtons).toHaveLength(2);

        act(() => {
            fireEvent.click(cancelButtons[0]);
        });

        expect(deleteConfirmationModal).not.toBeInTheDocument();

        act(() => {
            fireEvent.click(deleteButton);
        });

        cancelButtons = queryAllByRole('attribute-option-confirm-cancel-button');

        act(() => {
            fireEvent.click(cancelButtons[1]);
        });

        expect(deleteConfirmationModal).not.toBeInTheDocument();
    });

    test('an attribute option will not be moved upwards if the treshold is not met', async () => {
        const moveOptionCallback = jest.fn();
        const mockDragOverTarget = {
            getBoundingClientRect: jest.fn().mockImplementation(() => {
                return {bottom: 10, top: 0};
            }),
            clientY: 4
        };
        const {getByRole} = renderComponent(
            blueOption,
            jest.fn(),
            moveOptionCallback,
            jest.fn(),
            false,
            {code: 'black', index: 0},
            jest.fn()
        );
        const attributeOptionMoveHandle = getByRole('attribute-option-move-handle');

        act(() => {
            fireEvent.dragOver(attributeOptionMoveHandle, {
                target: mockDragOverTarget,
            });
        });

        expect(moveOptionCallback).not.toHaveBeenCalled();
    });

    test('an attribute option will not be moved downwards if the treshold is not met', async () => {
        const moveOptionCallback = jest.fn();
        const mockDragOverTarget = {
            getBoundingClientRect: jest.fn().mockImplementation(() => {
                return {bottom: 10, top: 0};
            }),
            clientY: 6
        };
        const {getByRole} = renderComponent(
            blueOption,
            jest.fn(),
            moveOptionCallback,
            jest.fn(),
            false,
            {code: 'black', index: 2},
            jest.fn()
        );
        const attributeOptionMoveHandle = getByRole('attribute-option-move-handle');

        act(() => {
            fireEvent.dragOver(attributeOptionMoveHandle, {
                target: mockDragOverTarget,
            });
        });

        expect(moveOptionCallback).not.toHaveBeenCalled();
    });

    test('an attribute option cannot replace itself', async () => {
        const moveOptionCallback = jest.fn();
        const {getByRole} = renderComponent(
            blackOption,
            jest.fn(),
            moveOptionCallback,
            jest.fn(),
            false,
            {code: 'black', index: 2},
            jest.fn()
        );
        const attributeOptionMoveHandle = getByRole('attribute-option-move-handle');

        act(() => {
            fireEvent.dragOver(attributeOptionMoveHandle);
        });

        expect(moveOptionCallback).not.toHaveBeenCalled();
    });

    test('an attribute option cannot be moved if the options are sorted alphabetically', async () => {
        const setDragItem = jest.fn();
        const {getByRole} = renderComponent(
            blackOption,
            jest.fn(),
            jest.fn(),
            jest.fn(),
            jest.fn(),
            true,
            setDragItem
        );
        const attributeOptionMoveHandle = getByRole('attribute-option-move-handle');

        act(() => {
            fireEvent.dragStart(attributeOptionMoveHandle);
        });
        expect(setDragItem).not.toHaveBeenCalled();

        act(() => {
            fireEvent.dragEnd(attributeOptionMoveHandle);
        });
        expect(setDragItem).not.toHaveBeenCalled();
    });
});

const renderComponent = (
    option,
    selectAttributeOptionCallback,
    moveAttributeOptionCallback,
    validateMoveAttributeOption,
    autoSort,
    dragItem,
    setDragItem
) => {
    return render(
        <DependenciesProvider>
            <Provider store={createStoreWithInitialState({attributeOptions: [option]})}>
                <AttributeContextProvider attributeId={8} autoSortOptions={autoSort}>
                    <AttributeOptionsContextProvider attributeId={8}>
                        <ListItem
                            data={option}
                            selectAttributeOption={selectAttributeOptionCallback}
                            isSelected={true}
                            moveAttributeOption={moveAttributeOptionCallback}
                            validateMoveAttributeOption={validateMoveAttributeOption}
                            dragItem={dragItem}
                            setDragItem={setDragItem}
                            index={1}
                        />
                    </AttributeOptionsContextProvider>
                </AttributeContextProvider>
            </Provider>
        </DependenciesProvider>
    );
};
