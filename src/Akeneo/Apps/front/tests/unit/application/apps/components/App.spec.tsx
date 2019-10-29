import {mount} from 'enzyme';
import {createMemoryHistory} from 'history';
import * as React from 'react';
import {MemoryRouter, Router} from 'react-router';
import {create} from 'react-test-renderer';
import {App} from '../../../../../src/application/apps/components/App';

describe('App', () => {
    it('should render', () => {
        const component = create(
            <MemoryRouter>
                <App code={'google-shopping'} label={'Google Shopping'} />
            </MemoryRouter>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should redirect to the app edit page when clicked', () => {
        const history = createMemoryHistory();
        const component = mount(
            <Router history={history}>
                <App code={'google-shopping'} label={'Google Shopping'} />
            </Router>
        );

        component.simulate('click');

        expect(history.location.pathname).toBe('/apps/google-shopping/edit');
    });
});
