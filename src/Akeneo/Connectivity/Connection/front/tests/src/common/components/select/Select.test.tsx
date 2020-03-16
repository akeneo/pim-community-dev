import {Select} from '@src/common';
import {fireEvent} from '@testing-library/react';
import React from 'react';
import {createWithProviders, renderWithProviders} from '../../../../test-utils';

describe('Select', () => {
    it('should render', () => {
        const component = createWithProviders(
            <Select
                data={{
                    'value-1': {label: 'label 1'},
                    'value-2': {label: 'label 2'},
                }}
                onChange={() => undefined}
                dropdownTitle='dropdown title'
            />
        );

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('calls onChange when an option is selected', async () => {
        const handleChange = jest.fn();

        const {getByText, findByText} = renderWithProviders(
            <Select
                data={{
                    'value-1': {label: 'label 1'},
                    'value-2': {label: 'label 2'},
                }}
                onChange={handleChange}
                dropdownTitle='dropdown title'
            />
        );

        const select = getByText('label 1');

        fireEvent.click(select);

        const option = await findByText('label 2');

        fireEvent.click(option);

        expect(handleChange).toBeCalledWith('value-2');
    });
});
