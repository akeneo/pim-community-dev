import React from 'react';
import * as ReactDOM from 'react-dom';
import {Provider} from "react-redux";
import '@testing-library/jest-dom/extend-expect';
import {act, getAllByRole, getByRole} from '@testing-library/react';
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

describe('Attribute options list', () => {
    test('it renders an empty attribute options list', async () => {
        global.fetch = jest.fn().mockImplementationOnce(route => {
            return {json: () => []};
        });

        await act(async () => {
            ReactDOM.render(
                <DependenciesProvider>
                    <Provider store={createStoreWithInitialState({})}>
                        <AttributeContextProvider attributeId={8}>
                            <List />
                        </AttributeContextProvider>
                    </Provider>
                </DependenciesProvider>,
                container
            );
        });

        expect(getByRole(container, 'attribute-options-list')).toBeEmpty();
    });

    test('it renders a list of 2 options', async () => {
        global.fetch = jest.fn().mockImplementationOnce(route => {
            return {
                json: () => [
                    {
                        "id": 85,
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
                ]
            };
        });

        await act(async () => {
            ReactDOM.render(
                <DependenciesProvider>
                    <Provider store={createStoreWithInitialState({})}>
                        <AttributeContextProvider attributeId={8}>
                            <List />
                        </AttributeContextProvider>
                    </Provider>
                </DependenciesProvider>,
                container
            );
        });

        const attributeOptions = getAllByRole(container, 'attribute-option-item');
        expect(attributeOptions.length).toBe(2);
        expect(attributeOptions[0].textContent).toBe('black');
        expect(attributeOptions[1].textContent).toBe('blue');
    });
});
