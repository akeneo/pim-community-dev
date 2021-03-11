import {useCallback} from 'react';
import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/legacy-bridge';

const Routing = require('routing');

type SSOConfigurationType = {
  configuration: {
    is_enabled: boolean;
    identity_provider_entity_id: string;
    identity_provider_sign_on_url: string;
    identity_provider_logout_url: string;
    identity_provider_certificate: string;
    service_provider_entity_id: string;
    service_provider_certificate: string;
    service_provider_private_key: string;
  };
};

type SSOConfigurationMetadata = {
  meta: {
    service_provider_certificate_expiration_date: string;
    service_provider_certificate_expires_soon: boolean;
    service_provider_metadata_url: string;
    service_provider_acs_url: string;
  };
};

type SSOConfigurationWithMetadata = SSOConfigurationType & SSOConfigurationMetadata;

type ValidationErrors = {
  [error: string]: string;
};

const useSSOConfiguration = () => {
  const notify = useNotify();
  const translate = useTranslate();

  const loadConfiguration = useCallback(async (): Promise<SSOConfigurationWithMetadata> => {
    const response = await fetch(Routing.generate('authentication_sso_configuration_get'));

    return await response.json();
  }, []);

  const saveConfiguration = async (
    isActive: boolean,
    IDPIdentityId: string,
    IDPSignOnUrl: string,
    IDPLogOutUrl: string,
    IDPCertificate: string,
    SPEntityId: string,
    SPCertificate: string,
    SPPrivateKey: string
  ) => {
    const response = await fetch(Routing.generate('authentication_sso_configuration_save'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: `${JSON.stringify({
        configuration: {
          is_enabled: isActive,
          identity_provider_entity_id: IDPIdentityId,
          identity_provider_sign_on_url: IDPSignOnUrl,
          identity_provider_logout_url: IDPLogOutUrl,
          identity_provider_certificate: IDPCertificate,
          service_provider_entity_id: SPEntityId,
          service_provider_certificate: SPCertificate,
          service_provider_private_key: SPPrivateKey,
        },
      })}`,
    });

    let formatedValidationErrors: ValidationErrors = {};

    if (response.ok) {
      notify(NotificationLevel.SUCCESS, translate('authentication.sso.configuration.info.update_successful'));
    } else {
      notify(NotificationLevel.ERROR, translate('authentication.sso.configuration.info.update_failed'));
      const errors = await response.json();
      if (typeof errors === 'object' && Object.keys(errors).length > 0) {
        Object.values(errors).forEach((error: {path: string; message: string}) => {
          formatedValidationErrors[error.path] = error.message;
        });
      }
    }

    return formatedValidationErrors;
  };

  return {
    loadConfiguration,
    saveConfiguration,
  };
};

export {useSSOConfiguration, SSOConfigurationType, SSOConfigurationWithMetadata, ValidationErrors};
