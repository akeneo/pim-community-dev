import {mount} from 'enzyme';
import {createMemoryHistory} from 'history';
import * as React from 'react';
import {MemoryRouter, Router} from 'react-router';
import {ThemeProvider} from 'styled-components';
import {theme} from '../../../../src/common/theme';
import {Connection} from '../../../../src/settings/components/Connection';
import {createWithTheme} from '../../../utils/create-with-theme';

describe('Connection', () => {
    it('should render', () => {
        const component = createWithTheme(
            <MemoryRouter>
                <Connection code={'google-shopping'} label={'Google Shopping'} image={'a/b/c/path.jpg'} />
            </MemoryRouter>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should redirect to the edit connection page when clicked', () => {
        const history = createMemoryHistory();
        const component = mount(
            <ThemeProvider theme={theme}>
                <Router history={history}>
                    <Connection code={'google-shopping'} label={'Google Shopping'} image={'a/b/c/path.jpg'} />
                </Router>
            </ThemeProvider>
        );

        component.simulate('click');

        expect(history.location.pathname).toBe('/connections/google-shopping/edit');
    });
});
