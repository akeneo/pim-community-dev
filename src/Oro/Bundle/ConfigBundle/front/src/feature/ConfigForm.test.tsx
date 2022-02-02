import React from 'react';
import { renderWithProviders } from '@akeneo-pim-community/shared';
import { screen, within, fireEvent, waitFor } from '@testing-library/react';
import { ConfigForm } from './ConfigForm'
import { ScopedValue } from './models/ConfigServicePayload';
import { GlobalWithFetchMock } from 'jest-fetch-mock';


const customGlobal: GlobalWithFetchMock = global as unknown as GlobalWithFetchMock;
customGlobal.fetch = require('jest-fetch-mock');
customGlobal.fetchMock = customGlobal.fetch;


function scopedValue<V>(value: V): ScopedValue<V> {
    return {
        value,
        scope: "app",
        use_parent_scope_value: false
    };
}

describe('configForm', () => {
    test('should display all elements of the page configuration', async () => {

        fetchMock.mockOnceIf(request => request.url === 'oro_config_configuration_system_get',
            JSON.stringify({
                pim_ui___language: scopedValue('fr_FR'),
                pim_analytics___version_update: scopedValue(true),
                pim_ui___loading_message_enabled: scopedValue(false),
                pim_ui___loading_messages: scopedValue("FOO")
            }))

        fetchMock.mockOnceIf(request => request.url === 'pim_localization_locale_index',
            JSON.stringify([
                {
                    "id": 58,
                    "code": "en_US",
                    "label": "English (United States)",
                    "region": "United States",
                    "language": "English"
                },
                {
                    "id": 90,
                    "code": "fr_FR",
                    "label": "French (France)",
                    "region": "France",
                    "language": "French"
                },

            ]))


        renderWithProviders(<ConfigForm />);


        const breadcrumbElt = await screen.findByLabelText('Breadcrumb');
        expect(within(breadcrumbElt).getByRole('link', { name: 'pim_menu.tab.system', })).toHaveAttribute('href', '#pim_system_index');
        expect(within(breadcrumbElt).getByText('pim_menu.item.configuration')).toHaveAttribute('aria-current', 'page');

        expect(screen.queryByRole('button', { 'name': 'Save' })).not.toBeNull();
        //expect(screen.queryByText('pim_menu.item.configuration')).not.toBeNull();
        expect(screen.queryByText('oro_config.form.config.group.loading_message.title')).not.toBeNull();
        expect(screen.queryByText('oro_config.form.config.group.loading_message.helper')).not.toBeNull();

        const loadingMessageEnablerLabelElt = screen.getByTestId('loading_message__enabler');
        expect(within(loadingMessageEnablerLabelElt).queryByText('oro_config.form.config.group.loading_message.fields.enabler.label')).not.toBeNull();
        expect(within(loadingMessageEnablerLabelElt).queryByRole('switch', { checked: false })).not.toBeNull();

        const textareaElt = screen.queryByLabelText('oro_config.form.config.group.loading_message.fields.messages.label');
        expect(textareaElt).toHaveValue('FOO');

        expect(screen.queryByText('oro_config.form.config.group.localization.title')).not.toBeNull();
        expect(screen.queryByText('oro_config.form.config.group.localization.fields.system_locale.label')).not.toBeNull();
        expect(screen.queryByLabelText('FR')).not.toBeNull();

        expect(screen.queryByText('oro_config.form.config.group.notification.title')).not.toBeNull();
        expect(screen.queryByText('oro_config.form.config.group.notification.helper')).not.toBeNull();
        const loadingMessageEnablerNotificationElt = screen.getByTestId('notification_message__enabler');
        expect(within(loadingMessageEnablerNotificationElt).queryByText('oro_config.form.config.group.notification.fields.enabler.label')).not.toBeNull();
        expect(within(loadingMessageEnablerNotificationElt).queryByRole('switch', { checked: true })).not.toBeNull();
    });

    test('should save the enable loading message value', async () => {
        fetchMock.mockOnceIf(request => request.url === 'oro_config_configuration_system_get' && request.method === 'GET',
            JSON.stringify({
                pim_ui___language: scopedValue('fr_FR'),
                pim_analytics___version_update: scopedValue(false),
                pim_ui___loading_message_enabled: scopedValue(false),
                pim_ui___loading_messages: scopedValue("FOO")
            }));

        fetchMock.mockOnceIf(request => request.url === 'pim_localization_locale_index',
            JSON.stringify([
                {
                    "id": 58,
                    "code": "en_US",
                    "label": "English (United States)",
                    "region": "United States",
                    "language": "English"
                },
                {
                    "id": 90,
                    "code": "fr_FR",
                    "label": "French (France)",
                    "region": "France",
                    "language": "French"
                },
            ]));


        const postRespond = jest.fn((request) => request.text());

        fetchMock.mockOnceIf(request => {
            return request.url === 'oro_config_configuration_system_get' && request.method === 'POST'
        }, postRespond);

        renderWithProviders(<ConfigForm />);

        fireEvent.click(within(await screen.findByTestId('loading_message__enabler')).getByTitle('pim_common.yes'));
        fireEvent.click(screen.getByRole('button', { 'name': 'Save' }));
        await waitFor(() => expect(postRespond).toHaveBeenCalled());
        expect(within(screen.getByTestId('loading_message__enabler')).queryByRole('switch', { checked: true })).not.toBeNull();
    });
});
