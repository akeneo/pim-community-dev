import React, { useCallback, useEffect, useState } from 'react';
import {
  NotificationLevel,
  PageContent,
  PageHeader,
  PimView,
  Section,
  useFetchSimpler,
  useNotify,
  useRoute,
  useTranslate
} from '@akeneo-pim-community/shared';
import {
  BooleanInput,
  Breadcrumb,
  Button,
  Field,
  Helper,
  SectionTitle,
  TextAreaInput
} from 'akeneo-design-system';
import { LocaleSelector } from './components/LocaleSelector';
import { configBackToFront, ConfigServicePayloadBackend, ConfigServicePayloadFrontend } from './models/ConfigServicePayload';

const ConfigForm = () => {
  const __ = useTranslate();
  const notify = useNotify();

  const systemHref = useRoute('pim_system_index');
  const configUrl = useRoute('oro_config_configuration_system_get');

  const [configFetchResult, doFetchConfig] = useFetchSimpler<ConfigServicePayloadBackend, ConfigServicePayloadFrontend>(configUrl, configBackToFront);

  // configuration object under edition
  const [config, setConfig] = useState<ConfigServicePayloadFrontend | null>(null);
  const [isModified, setIsModified] = useState(false);



  const modifyConfig = (config: ConfigServicePayloadFrontend) => {
    setConfig(config);
    setIsModified(true);
  }

  const handleBoolChange = useCallback((fieldName: 'pim_ui___loading_message_enabled' | 'pim_analytics___version_update') => {
    return (value: boolean) => {
      if (!config) return;
      modifyConfig(
        {
          ...config,
          [fieldName]: {
            ...config[fieldName],
            value
          }
        });
    }
  }, [config]);

  const handleStringChange = useCallback((fieldName: 'pim_ui___loading_messages' | 'pim_ui___language') => {
    return (value: string) => {
      if (!config) return;
      modifyConfig(
        {
          ...config,
          [fieldName]: {
            ...config[fieldName],
            value
          }
        });
    }
  }, [config]);

  const handleSave = async () => {
    const response = await fetch(configUrl, {
      method: 'POST',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
      body: JSON.stringify(config)
    });
    if (response.ok) {
      setConfig(configBackToFront(await response.json()));
      setIsModified(false);
      notify(NotificationLevel.SUCCESS, __('oro_config.form.config.save_ok'));
    } else {
      notify(NotificationLevel.ERROR, __('oro_config.form.config.save_error', { reason: response.statusText }));
    }
  }


  useEffect(() => {
    doFetchConfig();
  }, [doFetchConfig]);

  useEffect(() => {
    if (configFetchResult.type === 'fetched') {
      setConfig({ ...configFetchResult.payload });
    }
  }, [configFetchResult])

  if (configFetchResult.type === 'error') {
    return <Helper level="error">
      {__('Unexpected error occurred. Please contact system administrator.')}: {configFetchResult.message}
    </Helper>
  }

  if (!config) return null;


  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href={`#${systemHref}`}>{__('pim_menu.tab.system')}</Breadcrumb.Step>
            <Breadcrumb.Step>{__('pim_menu.item.configuration')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Actions>
          <Button onClick={handleSave}>Save</Button>
        </PageHeader.Actions>
        <PageHeader.State>
          {isModified && <Helper
            inline
            level="warning"
          >
            {__('pim_common.entity_updated')}
          </Helper>}
        </PageHeader.State>
        <PageHeader.Title>{__('pim_menu.item.configuration')}</PageHeader.Title>
      </PageHeader>
      <PageContent>
        <Section>
          <SectionTitle>
            <SectionTitle.Title>{__('oro_config.form.config.group.loading_message.title')}</SectionTitle.Title>
          </SectionTitle>
          <Helper level="info">
            {__('oro_config.form.config.group.loading_message.helper')}
          </Helper>
          <Field data-testid="loading_message__enabler" label={__('oro_config.form.config.group.loading_message.fields.enabler.label')}>
            <BooleanInput
              readOnly={false}
              value={config.pim_ui___loading_message_enabled.value}
              yesLabel={__('pim_common.yes')}
              noLabel={__('pim_common.no')}
              onChange={handleBoolChange('pim_ui___loading_message_enabled')}
            />
          </Field>
          <Field label={__('oro_config.form.config.group.loading_message.fields.messages.label')}>
            <TextAreaInput readOnly={false} value={config.pim_ui___loading_messages.value} onChange={handleStringChange('pim_ui___loading_messages')} placeholder={__('oro_config.form.config.group.loading_message.placeholder')} />
          </Field>
        </Section>
        <Section>
          <SectionTitle>
            <SectionTitle.Title>{__('oro_config.form.config.group.localization.title')}</SectionTitle.Title>
          </SectionTitle>
          <Field label={__('oro_config.form.config.group.localization.fields.system_locale.label')}>
            <LocaleSelector value={config.pim_ui___language.value} onChange={handleStringChange('pim_ui___language')} />
          </Field>
        </Section>
        <Section>
          <SectionTitle>
            <SectionTitle.Title>{__('oro_config.form.config.group.notification.title')}</SectionTitle.Title>
          </SectionTitle>
          <Helper level="info">
            {__('oro_config.form.config.group.notification.helper')}
          </Helper>
          <Field label={__('oro_config.form.config.group.notification.fields.enabler.label')}>
            <BooleanInput
              readOnly={false}
              value={config.pim_analytics___version_update.value}
              yesLabel={__('pim_common.yes')}
              noLabel={__('pim_common.no')}
              onChange={handleBoolChange('pim_analytics___version_update')}
            />
          </Field>
        </Section>
      </PageContent>
    </>
  );
};

export { ConfigForm };
