import * as React from 'react';
import {ConnectionGrid} from '@src/settings/components/ConnectionGrid';
import {createWithProviders} from '../../../test-utils';

describe('ConnectionGrid', () => {
    it('should render', () => {
        const component = createWithProviders(
            <ConnectionGrid title={'others'} connections={[]} wrongCredentialsCombinations={{}} />
        );

        expect(component.toJSON()).toMatchSnapshot();
    });
});
