import React, {useEffect as mockUseEffect} from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen, within, fireEvent, waitFor} from '@testing-library/react';
import {ConfigServicePayloadFrontend, ScopedValue} from './models/ConfigServicePayload';
import {ConfigForm} from './ConfigForm';

function mockScopedValue<V>(value: V): ScopedValue<V> {
  return {
    value,
    scope: 'app',
    use_parent_scope_value: false,
  };
}

const mockConfigPayload = {
  pim_ui___language: mockScopedValue('fr_FR'),
  pim_analytics___version_update: mockScopedValue(true),
  pim_ui___loading_message_enabled: mockScopedValue(false),
  pim_ui___loading_messages: mockScopedValue('FOO'),
};

const mockConfigFetchResult = {
  type: 'fetched',
  payload: mockConfigPayload,
};

let mockDoFetchConfig: jest.Mock;
let mockDoSaveConfig: jest.Mock;

jest.mock('./hooks', () => ({
  useFetchConfig: () => {
    mockUseEffect(() => {
      mockDoFetchConfig();
    }, []);
    return mockConfigFetchResult;
  },
  useSaveConfig: () => mockDoSaveConfig,
}));

jest.mock('./components/LocaleSelector', () => ({
  LocaleSelector: () => <span aria-label="FR"></span>,
}));

beforeEach(() => {
  mockDoFetchConfig = jest.fn();
  mockDoSaveConfig = jest.fn(async (config: ConfigServicePayloadFrontend) => config);
});

describe('configForm', () => {
  test('should display all elements of the configuration page', async () => {
    renderWithProviders(<ConfigForm />);

    expect(mockDoFetchConfig).toHaveBeenCalled();

    const breadcrumbElt = await screen.findByLabelText('Breadcrumb');
    expect(within(breadcrumbElt).getByRole('link', {name: 'pim_menu.tab.system'})).toHaveAttribute(
      'href',
      '#pim_system_index'
    );
    expect(within(breadcrumbElt).getByText('pim_menu.item.configuration')).toHaveAttribute('aria-current', 'page');

    expect(screen.queryByRole('button', {name: 'Save'})).not.toBeNull();
    //expect(screen.queryByText('pim_menu.item.configuration')).not.toBeNull();
    expect(screen.queryByText('oro_config.form.config.group.loading_message.title')).not.toBeNull();
    expect(screen.queryByText('oro_config.form.config.group.loading_message.helper')).not.toBeNull();

    const loadingMessageEnablerLabelElt = screen.getByTestId('loading_message__enabler');
    expect(
      within(loadingMessageEnablerLabelElt).queryByText(
        'oro_config.form.config.group.loading_message.fields.enabler.label'
      )
    ).not.toBeNull();
    expect(within(loadingMessageEnablerLabelElt).queryByRole('switch', {checked: false})).not.toBeNull();

    const textareaElt = screen.queryByLabelText('oro_config.form.config.group.loading_message.fields.messages.label');
    expect(textareaElt).toHaveValue('FOO');

    expect(screen.queryByText('oro_config.form.config.group.localization.title')).not.toBeNull();
    expect(screen.queryByText('oro_config.form.config.group.localization.fields.system_locale.label')).not.toBeNull();
    expect(screen.queryByLabelText('FR')).not.toBeNull();

    expect(screen.queryByText('oro_config.form.config.group.notification.title')).not.toBeNull();
    expect(screen.queryByText('oro_config.form.config.group.notification.helper')).not.toBeNull();
    const loadingMessageEnablerNotificationElt = screen.getByTestId('notification_message__enabler');
    expect(
      within(loadingMessageEnablerNotificationElt).queryByText(
        'oro_config.form.config.group.notification.fields.enabler.label'
      )
    ).not.toBeNull();
    expect(within(loadingMessageEnablerNotificationElt).queryByRole('switch', {checked: true})).not.toBeNull();
  });

  test('should save the enable loading message value', async function () {
    renderWithProviders(<ConfigForm />);

    fireEvent.click(within(await screen.findByTestId('loading_message__enabler')).getByTitle('pim_common.yes'));
    fireEvent.click(screen.getByRole('button', {name: 'Save'}));

    const expectedConfig = {
      ...mockConfigPayload,
      pim_ui___loading_message_enabled: mockScopedValue(true),
    };

    await waitFor(() => expect(mockDoSaveConfig).toHaveBeenCalledWith(expectedConfig));

    expect(
      within(await screen.findByTestId('loading_message__enabler')).queryByRole('switch', {checked: true})
    ).not.toBeNull();
  });
});
