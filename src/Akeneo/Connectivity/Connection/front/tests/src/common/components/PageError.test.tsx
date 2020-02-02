import {PageError} from '@src/common';
import React from 'react';
import {createWithProviders} from '../../../test-utils';

describe('PageError', () => {
    it('should render', () => {
        const component = createWithProviders(<PageError title='title'>content</PageError>);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
