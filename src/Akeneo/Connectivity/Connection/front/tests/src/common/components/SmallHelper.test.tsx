import {SmallHelper} from '@src/common';
import React from 'react';
import {createWithProviders} from '../../../test-utils';

describe('SmallHelper', () => {
    it('should render', () => {
        const component = createWithProviders(<SmallHelper>content</SmallHelper>);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
