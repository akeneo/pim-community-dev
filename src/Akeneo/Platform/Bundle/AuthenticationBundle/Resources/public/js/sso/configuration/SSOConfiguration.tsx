import React, {FC, useCallback, useEffect, useState} from 'react';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {
  BooleanInput,
  Breadcrumb,
  Button,
  Field,
  Helper,
  SectionTitle,
  TextInput,
  TextAreaInput,
} from 'akeneo-design-system';
import {PimView, useRouter, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import styled from 'styled-components';
import {
  SSOConfigurationType,
  SSOConfigurationWithMetadata,
  useSSOConfiguration,
  ValidationErrors,
} from './useSSOConfiguration';

type SSOConfigurationProps = {
  setCanLeavePage: (canLeave: boolean) => void;
  readonly: boolean;
};

const SSOConfiguration: FC<SSOConfigurationProps> = ({setCanLeavePage, readonly}) => {
  const translate = useTranslate();
  const router = useRouter();
  const [validationErrors, setValidationErrors] = useState<ValidationErrors>({});

  const [isConfigurationUpdated, setIsConfigurationUpdated] = useState<boolean>(false);
  const [originalConfiguration, setOriginalConfiguration] = useState<null | SSOConfigurationWithMetadata>(null);
  const {loadConfiguration, saveConfiguration} = useSSOConfiguration();

  const [isActive, setIsActive] = useState(false);
  const [IDPIdentityId, setIDPIdentityId] = useState<string>('');
  const [IDPSignOnUrl, setIDPSignOnUrl] = useState<string>('');
  const [IDPLogOutUrl, setIDPLogOutUrl] = useState<string>('');
  const [IDPCertificate, setIDPCertificate] = useState<string>('');
  const [SPEntityId, setSPEntityId] = useState<string>('');
  const [SPCertificate, setSPCertificate] = useState<string>('');
  const [SPPrivateKey, setSPPrivateKey] = useState<string>('');

  const handleUnload = useCallback(
    (event: BeforeUnloadEvent) => {
      if (isConfigurationUpdated) {
        event.preventDefault();
        event.returnValue = '';
      }

      return;
    },
    [isConfigurationUpdated]
  );

  useEffect(() => {
    window.addEventListener('beforeunload', handleUnload);

    return () => window.removeEventListener('beforeunload', handleUnload);
  }, [handleUnload]);

  useEffect(() => {
    (async () => {
      const SSOConfiguration = await loadConfiguration();
      setIsActive(SSOConfiguration.configuration.is_enabled);

      setIDPIdentityId(SSOConfiguration.configuration.identity_provider_entity_id);
      setIDPSignOnUrl(SSOConfiguration.configuration.identity_provider_sign_on_url);
      setIDPLogOutUrl(SSOConfiguration.configuration.identity_provider_logout_url);
      setIDPCertificate(SSOConfiguration.configuration.identity_provider_certificate);

      setSPEntityId(SSOConfiguration.configuration.service_provider_entity_id);
      setSPCertificate(SSOConfiguration.configuration.service_provider_certificate);
      setSPPrivateKey(SSOConfiguration.configuration.service_provider_private_key);

      setOriginalConfiguration(SSOConfiguration);
    })();
  }, []);

  const buildCurrentConfiguration = (): SSOConfigurationType => {
    return {
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
    };
  };

  useEffect(() => {
    if (originalConfiguration === null) {
      return;
    }

    const isConfigurationUpdated =
      JSON.stringify({configuration: originalConfiguration.configuration}) !==
      JSON.stringify(buildCurrentConfiguration());
    setIsConfigurationUpdated(isConfigurationUpdated);
    setCanLeavePage(!isConfigurationUpdated);
  }, [
    isActive,
    IDPIdentityId,
    IDPSignOnUrl,
    IDPLogOutUrl,
    IDPCertificate,
    SPEntityId,
    SPCertificate,
    SPPrivateKey,
    originalConfiguration,
    buildCurrentConfiguration,
  ]);

  const copyTextToClipboard = async (text: string) => {
    await navigator.clipboard.writeText(text);
  };

  const save = async () => {
    const errors = await saveConfiguration(
      isActive,
      IDPIdentityId,
      IDPSignOnUrl,
      IDPLogOutUrl,
      IDPCertificate,
      SPEntityId,
      SPCertificate,
      SPPrivateKey
    );
    if (Object.keys(errors).length > 0) {
      setValidationErrors(errors);
      return;
    }

    setIsConfigurationUpdated(false);
    //@ts-ignore
    setOriginalConfiguration({
      ...originalConfiguration,
      configuration: buildCurrentConfiguration().configuration,
    });
    setValidationErrors({});
    if (originalConfiguration && originalConfiguration.configuration.is_enabled !== isActive) {
      setTimeout(() => window.location.reload(), 1000);
    }
  };

  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step onClick={() => router.redirect(router.generate('oro_config_configuration_system'))}>
              {translate('pim_menu.tab.system')}
            </Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.sso')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Actions>
          <Button
            level="secondary"
            ghost={true}
            href={router.generate('authentication_sso_configuration_download_authentication_logs')}
          >
            {translate('authentication.sso.configuration.download_logs_button')}
          </Button>
          <Button level="primary" onClick={save} disabled={readonly}>
            {translate('pim_common.save')}
          </Button>
        </PageHeader.Actions>
        <PageHeader.State>
          {isConfigurationUpdated && (
            <div className="AknTitleContainer-state">
              <div className="updated-status">
                <span className="AknState">{translate('pim_common.entity_updated')}</span>
              </div>
            </div>
          )}
        </PageHeader.State>
        <PageHeader.Title>{translate('pim_menu.item.sso')}</PageHeader.Title>
      </PageHeader>
      <PageContent>
        <Section>
          <SectionTitle>
            <SectionTitle.Title>
              {translate('authentication.sso.configuration.section.activation.title')}
            </SectionTitle.Title>
          </SectionTitle>
          <Helper level="info">
            {translate('authentication.sso.configuration.section.activation.info.description')}
          </Helper>
          <StyledField label={translate('authentication.sso.configuration.field.activation.label')}>
            <BooleanInput
              clearLabel=""
              noLabel={translate('pim_common.no')}
              yesLabel={translate('pim_common.yes')}
              value={isActive}
              readOnly={readonly}
              onChange={setIsActive}
            />
          </StyledField>
        </Section>

        <Section>
          <SectionTitle>
            <SectionTitle.Title>
              {translate('authentication.sso.configuration.section.identity_provider.title')}
            </SectionTitle.Title>
          </SectionTitle>
          <Helper level="info">
            {translate('authentication.sso.configuration.section.identity_provider.info.description')}
          </Helper>
          <StyledField label={translate('authentication.sso.configuration.field.entity_id.label')}>
            <TextInput onChange={setIDPIdentityId} value={IDPIdentityId} readOnly={!isActive} />
            {validationErrors.hasOwnProperty('identity_provider_entity_id') ? (
              <Helper inline={true} level={'error'}>
                {validationErrors['identity_provider_entity_id']}
              </Helper>
            ) : (
              <></>
            )}
          </StyledField>

          <StyledField label={translate('authentication.sso.configuration.field.sign_on_url.label')}>
            <TextInput onChange={setIDPSignOnUrl} value={IDPSignOnUrl} readOnly={!isActive} />
            {validationErrors.hasOwnProperty('identity_provider_sign_on_url') ? (
              <Helper inline={true} level={'error'}>
                {validationErrors['identity_provider_sign_on_url']}
              </Helper>
            ) : (
              <></>
            )}
          </StyledField>

          <StyledField label={translate('authentication.sso.configuration.field.logout_url.label')}>
            <TextInput onChange={setIDPLogOutUrl} value={IDPLogOutUrl} readOnly={!isActive} />
            {validationErrors.hasOwnProperty('identity_provider_logout_url') ? (
              <Helper inline={true} level={'error'}>
                {validationErrors['identity_provider_logout_url']}
              </Helper>
            ) : (
              <></>
            )}
          </StyledField>

          <StyledField label={translate('authentication.sso.configuration.field.certificate.label')}>
            <TextAreaInput readOnly={!isActive} value={IDPCertificate} onChange={setIDPCertificate} />
            {validationErrors.hasOwnProperty('identity_provider_certificate') ? (
              <Helper inline={true} level={'error'}>
                {validationErrors['identity_provider_certificate']}
              </Helper>
            ) : (
              <></>
            )}
          </StyledField>
        </Section>

        <Section>
          <SectionTitle>
            <SectionTitle.Title>
              {translate('authentication.sso.configuration.section.service_provider.title')}
            </SectionTitle.Title>
          </SectionTitle>
          <Helper level="info">
            {translate('authentication.sso.configuration.section.service_provider.info.description')}
          </Helper>

          <StyledField label={translate('authentication.sso.configuration.field.metadata_url.label')}>
            <FieldContent>
              <TextInputContainer>
                <TextInput
                  onChange={() => {}}
                  value={originalConfiguration ? originalConfiguration.meta.service_provider_metadata_url : ''}
                  readOnly={true}
                />
              </TextInputContainer>
              <Button
                level="tertiary"
                ghost={true}
                onClick={() =>
                  copyTextToClipboard(
                    originalConfiguration ? originalConfiguration.meta.service_provider_metadata_url : ''
                  )
                }
              >
                {translate('authentication.sso.configuration.field.copy')}
              </Button>
            </FieldContent>
          </StyledField>

          <StyledField label={translate('authentication.sso.configuration.field.acs_url.label')}>
            <FieldContent>
              <TextInputContainer>
                <TextInput
                  onChange={() => {}}
                  value={originalConfiguration ? originalConfiguration.meta.service_provider_acs_url : ''}
                  readOnly={true}
                />
              </TextInputContainer>
              <Button
                level="tertiary"
                ghost={true}
                onClick={() =>
                  copyTextToClipboard(originalConfiguration ? originalConfiguration.meta.service_provider_acs_url : '')
                }
              >
                {translate('authentication.sso.configuration.field.copy')}
              </Button>
            </FieldContent>
          </StyledField>

          <StyledField label={translate('authentication.sso.configuration.field.entity_id.label')}>
            <TextInput onChange={setSPEntityId} value={SPEntityId} readOnly={!isActive} />
            {validationErrors.hasOwnProperty('service_provider_entity_id') ? (
              <Helper inline={true} level={'error'}>
                {validationErrors['service_provider_entity_id']}
              </Helper>
            ) : (
              <></>
            )}
          </StyledField>

          <StyledField label={translate('authentication.sso.configuration.field.certificate.label')}>
            <TextAreaInput readOnly={!isActive} value={SPCertificate} onChange={setSPCertificate} />
            {originalConfiguration && originalConfiguration.meta.service_provider_certificate_expiration_date ? (
              <Helper
                inline
                level={originalConfiguration.meta.service_provider_certificate_expires_soon ? 'warning' : 'info'}
              >
                {translate('authentication.sso.configuration.field.certificate.expiration_warning', {
                  date: originalConfiguration.meta.service_provider_certificate_expiration_date,
                })}
              </Helper>
            ) : (
              <></>
            )}
            {validationErrors.hasOwnProperty('service_provider_certificate') ? (
              <Helper inline={true} level={'error'}>
                {validationErrors['service_provider_certificate']}
              </Helper>
            ) : (
              <></>
            )}
          </StyledField>

          <StyledField label={translate('authentication.sso.configuration.field.private_key.label')}>
            <TextAreaInput readOnly={!isActive} value={SPPrivateKey} onChange={setSPPrivateKey} />
            {validationErrors.hasOwnProperty('service_provider_private_key') ? (
              <Helper inline={true} level={'error'}>
                {validationErrors['service_provider_private_key']}
              </Helper>
            ) : (
              <></>
            )}
          </StyledField>
        </Section>
      </PageContent>
    </>
  );
};

const StyledField = styled(Field)`
  margin-top: 20px;
`;

const Section = styled.div`
  margin-bottom: 20px;
`;

const FieldContent = styled.div`
  display: flex;
  align-items: center;
`;

const TextInputContainer = styled.div`
  margin-right: 10px;

  input {
    min-width: 460px;
  }
`;

export {SSOConfiguration};
