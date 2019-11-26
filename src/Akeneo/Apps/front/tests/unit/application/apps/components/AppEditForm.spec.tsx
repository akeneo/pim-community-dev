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

const formik = {
    values: {
        label: bynderApp.label,
        flowType: bynderApp.flowType,
    },
    errors: {
        label: 'error_message',
    },
    handleChange: jest.fn().mockName('handleChange'),
} as any;

describe('App', () => {
    it('should render', () => {
        const component = createWithTheme(
            <ThemeProvider theme={theme}>
                <AppEditForm app={bynderApp} formik={formik} />
            </ThemeProvider>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });
});
