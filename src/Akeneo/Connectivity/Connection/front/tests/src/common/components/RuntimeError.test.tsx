import {RuntimeError} from '@src/common';
import React from 'react';
import {createWithProviders} from '../../../test-utils';

describe('RuntimeError', () => {
    it('should render', () => {
        const component = createWithProviders(<RuntimeError />);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
