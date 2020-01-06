import * as React from 'react';
import {ConnectionGrid} from '../../../../src/settings/components/ConnectionGrid';
import {createWithTheme} from '../../../utils/create-with-theme';

describe('ConnectionGrid', () => {
    it('should render', () => {
        const component = createWithTheme(<ConnectionGrid title={'others'} connections={[]} />);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
