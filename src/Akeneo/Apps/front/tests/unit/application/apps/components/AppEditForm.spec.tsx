import {mount} from 'enzyme';
import * as React from 'react';
import {ThemeProvider} from 'styled-components';
import {AppEditForm} from '../../../../../src/application/apps/components/AppEditForm';
import {theme} from '../../../../../src/application/common/theme';
import {App} from '../../../../../src/domain/apps/app.interface';
import {FlowType} from '../../../../../src/domain/apps/flow-type.enum';
import {createWithTheme} from '../../../../utils/create-with-theme';

jest.mock('../../../../../src/application/common/components/Select2', () => ({
    Select2: ({value}: {value: string}) => <input type='hidden' value={value} />,
}));

const bynderApp: App = {
    code: 'bynder',
    label: 'Bynder',
    flowType: FlowType.DATA_SOURCE,
};

describe('App', () => {
    it('should render', () => {
        const component = createWithTheme(
            <ThemeProvider theme={theme}>
                <AppEditForm app={bynderApp} onChange={() => undefined} />
            </ThemeProvider>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should trigger the `onChange` callback after a change in the form', () => {
        const handleChange = jest.fn();

        const component = mount(
            <ThemeProvider theme={theme}>
                <AppEditForm app={bynderApp} onChange={handleChange} />
            </ThemeProvider>
        );

        const input = component.find('input[name="label"]');

        input.simulate('change');
        (input.instance() as any).value = '';
        input.simulate('change');

        expect(handleChange).toHaveBeenNthCalledWith(1, {hasUnsavedChanges: true, isValid: true});
        expect(handleChange).toHaveBeenNthCalledWith(2, {hasUnsavedChanges: true, isValid: false});
    });

    it('should display an error message if the label input is empty', () => {
        const handleChange = jest.fn();

        const component = mount(
            <ThemeProvider theme={theme}>
                <AppEditForm app={bynderApp} onChange={handleChange} />
            </ThemeProvider>
        );

        const input = component.find('input[name="label"]');

        (input.instance() as any).value = '';
        input.simulate('change');

        expect(component.text()).toContain('akeneo_apps.constraint.label.required');
    });
});
