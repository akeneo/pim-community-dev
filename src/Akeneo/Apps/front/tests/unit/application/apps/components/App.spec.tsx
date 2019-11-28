import {mount} from 'enzyme';
import {createMemoryHistory} from 'history';
import * as React from 'react';
import {MemoryRouter, Router} from 'react-router';
import {ThemeProvider} from 'styled-components';
import {App} from '../../../../../src/application/apps/components/App';
import {theme} from '../../../../../src/application/common/theme';
import {createWithTheme} from '../../../../utils/create-with-theme';

describe('App', () => {
    it('should render', () => {
        const component = createWithTheme(
            <MemoryRouter>
                <App code={'google-shopping'} label={'Google Shopping'} />
            </MemoryRouter>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should redirect to the app edit page when clicked', () => {
        const history = createMemoryHistory();
        const component = mount(
            <ThemeProvider theme={theme}>
                <Router history={history}>
                    <App code={'google-shopping'} label={'Google Shopping'} />
                </Router>
            </ThemeProvider>
        );

        component.simulate('click');

        expect(history.location.pathname).toBe('/apps/google-shopping/edit');
    });
});
