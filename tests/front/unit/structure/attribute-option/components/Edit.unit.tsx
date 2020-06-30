import React from 'react';
import {Provider} from 'react-redux';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, render} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import Edit from 'akeneopimstructure/js/attribute-option/components/Edit';
import {AttributeContextProvider, LocalesContext} from 'akeneopimstructure/js/attribute-option/contexts';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';
import {createStoreWithInitialState} from 'akeneopimstructure/js/attribute-option/store/store';
import {useAttributeOptionsContext} from 'akeneopimstructure/js/attribute-option/hooks/useAttributeOptionsContext';

jest.mock('akeneopimstructure/js/attribute-option/hooks/useAttributeOptionsContext');

const option: AttributeOption = {
    id: 80,
    code: 'black',
    optionValues: {
        en_US: {
            id: 1,
            value: 'Black',
            locale: 'en_US',
        },
        fr_FR: {
            id: 2,
            value: 'Noir',
            locale: 'fr_FR',
        },
    },
};

const renderEditWithContext = () => {
    const locales = [
        {'code':'en_US', 'label':'English (United States)'},
        {'code':'fr_FR', 'label':'French (France)'},
    ];

    return render(
        <DependenciesProvider>
            <Provider store={createStoreWithInitialState({attributeOptions: [option]})}>
                <AttributeContextProvider attributeId={8} autoSortOptions={true}>
                    <LocalesContext.Provider value={locales}>
                        <Edit option={option} />
                    </LocalesContext.Provider>
                </AttributeContextProvider>
            </Provider>
        </DependenciesProvider>
    );
};

describe('Edit an attribute option', () => {
    const mockSave = jest.fn();

    beforeAll(() => {
        useAttributeOptionsContext.mockImplementation(() => {
            return {
                save: mockSave
            };
        });
    });

    beforeEach(() => {
        jest.clearAllMocks();
    });

    afterAll(() => {
        jest.restoreAllMocks();
    });

    test('it renders an option form', async () => {
        const {queryAllByRole, getByRole} = renderEditWithContext();

        const translations = queryAllByRole('attribute-option-label');
        expect(translations.length).toBe(2);

        const saveButton = getByRole('save-options-translations');
        expect(saveButton).toBeInTheDocument();

        act(() => {
            fireEvent.change(translations[0], {target: {value: 'Black 2'}});
        });

        act(() => {
            fireEvent.click(saveButton);
        });

        let expectedOption: AttributeOption = expect.objectContaining({
            id: 80,
            code: 'black',
            optionValues: {
                en_US: {
                    id: 1,
                    value: 'Black 2',
                    locale: 'en_US',
                },
                fr_FR: {
                    id: 2,
                    value: 'Noir',
                    locale: 'fr_FR',
                },
            },
        });

        expect(mockSave).toHaveBeenNthCalledWith(1, expectedOption);
    });
});
