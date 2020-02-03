import * as React from 'react';
import {Section} from '@src/common';
import {createWithProviders} from '../../test-utils';

describe('Section', () => {
    it('should render', () => {
        const component = createWithProviders(<Section title={'title'} />);

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should render content', () => {
        const component = createWithProviders(<Section title={'title'}>content</Section>);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
