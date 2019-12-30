import {mount} from 'enzyme';
import * as React from 'react';
import {create} from 'react-test-renderer';
import {ThemeProvider} from 'styled-components';
import {NoApp} from '../../../src/apps/components/NoApp';
import {theme} from '../../../src/common/theme';

describe('NoApp', () => {
    it('should render', () => {
        const component = create(
            <ThemeProvider theme={theme}>
                <NoApp onCreate={() => undefined} />
            </ThemeProvider>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should call `onCreate` when the create app shortcut is clicked', () => {
        const handleCreate = jest.fn();

        const component = mount(
            <ThemeProvider theme={theme}>
                <NoApp onCreate={handleCreate} />
            </ThemeProvider>
        );

        component.find('a').simulate('click');
        expect(handleCreate).toBeCalled();
    });
});
