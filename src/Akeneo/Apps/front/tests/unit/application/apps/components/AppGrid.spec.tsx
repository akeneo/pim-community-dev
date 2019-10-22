import * as React from 'react';
import {create} from 'react-test-renderer';
import {AppGrid} from '../../../../../src/application/apps/components/AppGrid';

describe('AppGrid', () => {
    it('should render', () => {
        const component = create(<AppGrid title={'others'} apps={[]} />);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
