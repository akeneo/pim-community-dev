import * as React from 'react';
import {create} from 'react-test-renderer';
import {Figure, FigureCaption, FigureImage} from '../../../../src/application/common';

describe('Figure', () => {
    it('should render', () => {
        const component = create(
            <Figure>
                <FigureImage />
                <FigureCaption />
            </Figure>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });
});
