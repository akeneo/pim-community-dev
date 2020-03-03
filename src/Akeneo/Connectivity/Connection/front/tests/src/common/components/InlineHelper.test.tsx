import {InlineHelper} from '@src/common';
import React from 'react';
import {createWithProviders} from '../../../test-utils';

describe('InlineHelper', () => {
    it('should render', () => {
        const component = createWithProviders(<InlineHelper>content</InlineHelper>);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
