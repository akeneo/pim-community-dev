import React from 'react';
import * as ReactDOM from 'react-dom';
import {Provider} from "react-redux";
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, getAllByRole, getByRole, queryByRole, queryAllByRole} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {createStoreWithInitialState} from 'akeneopimstructure/js/attribute-option/store/store';
import List from 'akeneopimstructure/js/attribute-option/components/List';
import {AttributeContextProvider} from 'akeneopimstructure/js/attribute-option/contexts';

declare global {
    namespace NodeJS {
        interface Global {
            fetch: any;
        }
    }
}

let container: HTMLElement;

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
});

afterEach(() => {
    document.body.removeChild(container);
    global.fetch && global.fetch.mockClear();
    delete global.fetch;
});

const options = [
    {
        "id": 18,
        "code": "black",
        "optionValues": {
            "en_US": {"id":252,"locale":"en_US","value":"Black"},
            "fr_FR":{"id":253,"locale":"fr_FR","value":"Noir"}
        }
    },
    {
        "id": 86,
        "code": "blue",
        "optionValues": {
            "en_US": {"id":255,"locale":"en_US","value":"Blue"},
            "fr_FR":{"id":256,"locale":"fr_FR","value":"Bleu"}
        }
    },
];

describe('Attribute options list', () => {
    test('it renders an empty attribute options list', async () => {
        global.fetch = jest.fn().mockImplementationOnce(route => {
            return {json: () => []};
        });

        await renderComponent(jest.fn(), jest.fn());

        expect(getByRole(container, 'attribute-options-list')).toBeEmpty();
    });

    test('it renders a list of 2 options', async () => {
        global.fetch = jest.fn().mockImplementationOnce(route => {
            return {json: () => options};
        });

        await renderComponent(jest.fn(), jest.fn());

        const attributeOptions = getAllByRole(container, 'attribute-option-item');
        expect(attributeOptions.length).toBe(2);
        expect(attributeOptions[0].textContent).toBe('black');
        expect(attributeOptions[1].textContent).toBe('blue');

        expect(queryByRole(container, 'new-option-placeholder')).toBeNull();
    });

    test('it displays a fake new option at the end when adding a new option', async () => {
        global.fetch = jest.fn().mockImplementationOnce(route => {
            return {json: () => options};
        });

        const showNewOptionFormCallback = jest.fn();
        await renderComponent(jest.fn(), showNewOptionFormCallback);

        expect(queryByRole(container, 'new-option-placeholder')).toBeNull();

        const addNewOptionButton = getByRole(container, 'add-new-attribute-option-button');
        await fireEvent.click(addNewOptionButton);
        const newOptionPlaceholder = getByRole(container, 'new-option-placeholder');

        expect(newOptionPlaceholder).toBeInTheDocument();
        expect(showNewOptionFormCallback).toHaveBeenNthCalledWith(1, true);
    });

    test('it allows option selection', async () => {
        global.fetch = jest.fn().mockImplementationOnce(route => {
            return {json: () => options};
        });

        const selectOptionCallback = jest.fn();
        const blueOptionId = 86;
        await renderComponent(selectOptionCallback, jest.fn(), blueOptionId);

        expect(queryByRole(container, 'new-option-placeholder')).toBeNull();

        const optionItems = queryAllByRole(container, 'attribute-option-item');
        const blackOption = optionItems[0];
        const blueOption = optionItems[1];
        expect(blueOption).toHaveClass('AknAttributeOption-listItem--selected');
        expect(blackOption).not.toHaveClass('AknAttributeOption-listItem--selected');

        const optionLabels = queryAllByRole(container, 'attribute-option-item-label');
        const blackOptionLabel = optionLabels[0];
        await fireEvent.click(blackOptionLabel);

        const blackOptionId = 18;
        expect(selectOptionCallback).toHaveBeenNthCalledWith(1, blackOptionId)
    });
});

async function renderComponent(selectOptionCallback, showNewOptionFormCallback, selectedOptionId = null) {
    await act(async () => {
        ReactDOM.render(
          <DependenciesProvider>
              <Provider store={createStoreWithInitialState({})}>
                  <AttributeContextProvider attributeId={8}>
                      <List selectAttributeOption={selectOptionCallback} showNewOptionForm={showNewOptionFormCallback} selectedOptionId={selectedOptionId}/>
                  </AttributeContextProvider>
              </Provider>
          </DependenciesProvider>,
          container
        );
    });
}
