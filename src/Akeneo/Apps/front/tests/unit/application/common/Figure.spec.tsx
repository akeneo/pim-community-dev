import * as React from 'react';
import {Figure, FigureCaption, FigureImage} from '../../../../src/application/common';
import {createWithTheme} from '../../../utils/create-with-theme';

describe('Figure', () => {
    it('should render', () => {
        const component = createWithTheme(
            <Figure>
                <FigureImage />
                <FigureCaption />
            </Figure>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });
});
