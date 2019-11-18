import * as React from 'react';
import {create} from 'react-test-renderer';
import {ThemeProvider} from 'styled-components';
import {Section} from '../../../../src/application/common';
import {theme} from '../../../../src/application/common/theme';
import {createWithTheme} from '../../../utils/create-with-theme';

describe('Section', () => {
    it('should render', () => {
        const component = create(
            <ThemeProvider theme={theme}>
                <Section title={'title'} />
            </ThemeProvider>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should render content', () => {
        const component = createWithTheme(<Section title={'title'}>content</Section>);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
