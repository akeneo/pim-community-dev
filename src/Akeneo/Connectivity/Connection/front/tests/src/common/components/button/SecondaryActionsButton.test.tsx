import {SecondaryActionsDropdownButton, DropdownLink} from '@src/common';
import React from 'react';
import {createWithProviders} from '../../../../test-utils';

describe('SecondaryActionsDropdownButton', () => {
    it('should render', () => {
        const component = createWithProviders(
            <SecondaryActionsDropdownButton>
                <DropdownLink>link 1</DropdownLink>
                <DropdownLink>link 2</DropdownLink>
            </SecondaryActionsDropdownButton>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });
});
