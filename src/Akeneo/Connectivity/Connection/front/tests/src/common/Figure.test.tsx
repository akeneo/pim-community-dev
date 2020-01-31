import * as React from 'react';
import {Figure, FigureCaption, FigureImage} from '@src/common';
import {createWithProviders} from '../../test-utils';

describe('Figure', () => {
    it('should render', () => {
        const component = createWithProviders(
            <Figure>
                <FigureImage />
                <FigureCaption />
            </Figure>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });
});
