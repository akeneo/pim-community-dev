import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {
  configBackToFront,
  configFrontToBack,
  ConfigServicePayloadBackend,
  ConfigServicePayloadFrontend,
} from '../models/ConfigServicePayload';

export function useSaveConfig() {
  const translate = useTranslate();
  const notify = useNotify();
  const configUrl = useRoute('oro_config_configuration_system_get');

  return async function doSave(config: ConfigServicePayloadFrontend) {
    const response = await fetch(configUrl, {
      method: 'POST',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
      body: JSON.stringify(configFrontToBack(config)),
    });
    if (response.ok) {
      notify(NotificationLevel.SUCCESS, translate('oro_config.form.config.save_ok'));
      return configBackToFront((await response.json()) as unknown as ConfigServicePayloadBackend);
    }
    notify(NotificationLevel.ERROR, translate('oro_config.form.config.save_error', {reason: response.statusText}));
    return config;
  };
}
