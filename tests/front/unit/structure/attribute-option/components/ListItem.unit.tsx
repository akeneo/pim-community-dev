import React, {ReactElement} from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, createEvent, render} from '@testing-library/react';
import ListItem, {DragItem} from 'akeneopimstructure/js/attribute-option/components/ListItem';
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

const mockSelectAttributeOption = jest.fn();
const mockMoveAttributeOption = jest.fn();
const mockValidateMoveAttributeOption = jest.fn();
const mockSetDragItem = jest.fn();

const renderComponent = (
    option: AttributeOption,
    autoSort: boolean = false,
    dragItem: DragItem|null = null,
    isSelected: boolean = true,
    children?: ReactElement
) => {
    return render(
        <DependenciesProvider>
            <Provider store={createStoreWithInitialState({attributeOptions: [option]})}>
                <AttributeContextProvider attributeId={8} autoSortOptions={autoSort}>
                    <AttributeOptionsContextProvider>
                        <ListItem
                            data={option}
                            selectAttributeOption={mockSelectAttributeOption}
                            isSelected={isSelected}
                            moveAttributeOption={mockMoveAttributeOption}
                            validateMoveAttributeOption={mockValidateMoveAttributeOption}
                            dragItem={dragItem}
                            setDragItem={mockSetDragItem}
                            index={1}
                        >
                            {children}
                        </ListItem>
                    </AttributeOptionsContextProvider>
                </AttributeContextProvider>
            </Provider>
        </DependenciesProvider>
    );
};

describe('Attribute options list item', () => {
    beforeEach(() => {
        jest.clearAllMocks();
    });

    afterAll(() => {
        jest.resetAllMocks();
    });

    test('it renders a selected list item', async () => {
        const {getByRole} = renderComponent(blackOption);

        const attributeOption = getByRole('attribute-option-item');
        expect(attributeOption).toHaveClass('AknAttributeOption-listItem--selected');

        const attributeOptionLabel = getByRole('attribute-option-item-label');
        act(() => {
            fireEvent.click(attributeOptionLabel);
        });
        expect(mockSelectAttributeOption).toHaveBeenNthCalledWith(1, 80);
    });



    test('it renders a not selected list item ', async () => {
        const {getByRole} = renderComponent(blackOption, false, null, false);

        const attributeOption = getByRole('attribute-option-item');
        expect(attributeOption).not.toHaveClass('AknAttributeOption-listItem--selected');
    });

    test('it allows attribute option to be deleted', async () => {
        const {getByRole, queryByRole, queryAllByRole} = renderComponent(blackOption);

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

        await act(async () => {
            fireEvent.click(confirmDeleteButton);
        });

        expect(deleteConfirmationModal).not.toBeInTheDocument();
    });

    test('the attribute option deletion can be cancelled with the 2 cancel buttons', async () => {
        const {getByRole, queryAllByRole} = renderComponent(blackOption);

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
        const mockDragItem = {code: 'black', index: 0};
        const mockDragOverTarget = {
            getBoundingClientRect: jest.fn().mockImplementation(() => {
                return {bottom: 10, top: 0};
            }),
            clientY: 4
        };
        const {getByRole} = renderComponent(blueOption, false, mockDragItem);
        const attributeOptionMoveHandle = getByRole('attribute-option-move-handle');

        act(() => {
            fireEvent.dragOver(attributeOptionMoveHandle, {
                target: mockDragOverTarget,
            });
        });

        expect(mockMoveAttributeOption).not.toHaveBeenCalled();
    });

    test('an attribute option will be moved upwards if the treshold is met', async () => {
        const mockDragItem = {code: 'red', index: 0};
        const mockDragOverTarget = {
            getBoundingClientRect: jest.fn().mockImplementation(() => {
                return {bottom: 10, top: 0};
            }),
            clientY: 6
        };
        const {getByRole} = renderComponent(blueOption, false, mockDragItem);
        const attributeOptionMoveHandle = getByRole('attribute-option-move-handle');

        act(() => {
            fireEvent.dragOver(attributeOptionMoveHandle, {
                target: mockDragOverTarget,
            });
        });

        expect(mockMoveAttributeOption).toHaveBeenCalled();
    });

    test('an attribute option will not be moved downwards if the treshold is not met', async () => {
        const mockDragItem = {code: 'black', index: 2};
        const mockDragOverTarget = {
            getBoundingClientRect: jest.fn().mockImplementation(() => {
                return {bottom: 10, top: 0};
            }),
            clientY: 6
        };
        const {getByRole} = renderComponent(blueOption, false, mockDragItem);
        const attributeOptionMoveHandle = getByRole('attribute-option-move-handle');

        act(() => {
            fireEvent.dragOver(attributeOptionMoveHandle, {
                target: mockDragOverTarget,
            });
        });

        expect(mockMoveAttributeOption).not.toHaveBeenCalled();
    });

    test('an attribute option will be moved downwards if the treshold is met', async () => {
        const mockDragItem = {code: 'red', index: 3};
        const mockDragOverTarget = {
            getBoundingClientRect: jest.fn().mockImplementation(() => {
                return {bottom: 10, top: 0};
            }),
            clientY: 4
        };
        const {getByRole} = renderComponent(blueOption, false, mockDragItem);
        const attributeOptionMoveHandle = getByRole('attribute-option-move-handle');

        act(() => {
            fireEvent.dragOver(attributeOptionMoveHandle, {
                target: mockDragOverTarget,
            });
        });

        expect(mockMoveAttributeOption).toHaveBeenCalled();
    });

    test('an attribute option cannot replace itself', async () => {
        const mockDragItem = {code: 'black', index: 2};
        const {getByRole} = renderComponent(blackOption, false, mockDragItem);
        const attributeOptionMoveHandle = getByRole('attribute-option-move-handle');

        act(() => {
            fireEvent.dragOver(attributeOptionMoveHandle);
        });

        expect(mockMoveAttributeOption).not.toHaveBeenCalled();
    });

    test('an attribute option cannot be moved if the options are sorted alphabetically', async () => {
        const {getByRole} = renderComponent(blackOption, true);
        const attributeOptionMoveHandle = getByRole('attribute-option-move-handle');

        act(() => {
            fireEvent.dragStart(attributeOptionMoveHandle);
        });
        expect(mockSetDragItem).not.toHaveBeenCalled();

        act(() => {
            fireEvent.dragEnd(attributeOptionMoveHandle);
        });
        expect(mockSetDragItem).not.toHaveBeenCalled();
    });

    test('an attribute option cannot be moved if the user try to drag outside of the move icon', async () => {
        const {getByRole} = renderComponent(blackOption, false);
        const attributeOptionMoveHandle = getByRole('attribute-option-item-label');

        act(() => {
            fireEvent.dragStart(attributeOptionMoveHandle);
        });
        expect(mockSetDragItem).not.toHaveBeenCalled();

        act(() => {
            fireEvent.drop(attributeOptionMoveHandle);
        });
        expect(mockSetDragItem).not.toHaveBeenCalled();
    });

    test('an attribute option can be moved if the options are manually sorted', async () => {
        const {getByRole} = renderComponent(blackOption, false);
        const attributeOptionMoveHandle = getByRole('attribute-option-move-handle');
        const mockDragItem = {code: 'black', index: 1};

        act(() => {
            const dragStartEvent = createEvent.dragStart(attributeOptionMoveHandle);
            const setDragImage = jest.fn();
            Object.assign(dragStartEvent, {
                dataTransfer: {
                    setDragImage,
                },
            });

            fireEvent(attributeOptionMoveHandle, dragStartEvent);
        });
        expect(mockSetDragItem).toHaveBeenCalledWith(mockDragItem);
    });

    test('it reinitialize the dragged item on drop', () => {
        const mockDragItem = {code: 'black', index: 1};

        const {getByRole} = renderComponent(blackOption, false, mockDragItem);
        const attributeOptionMoveHandle = getByRole('attribute-option-move-handle');

        act(() => {
            fireEvent.drop(attributeOptionMoveHandle);
        });

        expect(mockSetDragItem).toHaveBeenCalledWith(null);
        expect(mockValidateMoveAttributeOption).toHaveBeenCalled();
    });

    test('it does not reinitialize drag item if it still null', () => {
        const {getByRole} = renderComponent(blackOption, false);
        const attributeOptionMoveHandle = getByRole('attribute-option-move-handle');

        act(() => {
            fireEvent.drop(attributeOptionMoveHandle);
        });

        expect(mockSetDragItem).not.toHaveBeenCalled();
        expect(mockValidateMoveAttributeOption).not.toHaveBeenCalled();
    });

    test('it renders an attribute option with extra data', () => {
        const {getByRole} = renderComponent(blackOption, false, null, false, (
            <div role={'extra-data-test'}/>
        ));

        const extraDataElement = getByRole('extra-data-test');
        expect(extraDataElement).toBeInTheDocument();
    });
});
