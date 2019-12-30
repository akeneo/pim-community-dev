import * as React from 'react';
import {AppGrid} from '../../../src/apps/components/AppGrid';
import {createWithTheme} from '../../utils/create-with-theme';

describe('AppGrid', () => {
    it('should render', () => {
        const component = createWithTheme(<AppGrid title={'others'} apps={[]} />);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
