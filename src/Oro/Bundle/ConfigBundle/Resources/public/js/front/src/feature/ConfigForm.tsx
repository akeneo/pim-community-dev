import React, { useEffect } from 'react';
import {
  Locale,
  PageContent,
  PageHeader,
  PimView,
  Section,
  useFetchSimpler,
  useRoute,
  useTranslate
} from '@akeneo-pim-community/shared';
import {
  BooleanInput,
  Breadcrumb,
  Button,
  Field,
  FieldProps,
  Helper,
  Locale as LocaleComponent,
  SectionTitle,
  SelectInput,
  TextAreaInput
} from 'akeneo-design-system';

import { configBackToFront, ConfigServicePayloadBackend, ConfigServicePayloadFrontend } from '../models/ConfigServicePayload';

const ConfigForm = () => {
  const systemHref = useRoute('pim_system_index');
  const localeUrl = useRoute('pim_localization_locale_index');
  const configUrl = useRoute('oro_config_configuration_system_get');

  const [localesFetchResult, doFetchLocales] = useFetchSimpler<Locale[]>(localeUrl);
  const [configFetchResult, doFetchConfig] = useFetchSimpler<ConfigServicePayloadBackend, ConfigServicePayloadFrontend>(configUrl, configBackToFront);

  const __ = useTranslate();

  // TODO fetch only ones by session
  useEffect(() => { doFetchLocales(); doFetchConfig() }, []);

  switch (configFetchResult.type) {
    case 'idle':  // intentional no break
    case 'fetching':
      return null;
    case 'error':
      return <Helper
        inline
        level="error"
      >
        {__('Unexpected error occurred. Please contact system administrator.')}: {configFetchResult.message}
      </Helper>
    case 'fetched': // intentional no break
    default:
    // continue to rendering
  }

  let localesElement: FieldProps['children'] = <Helper
    inline
    level="info"
  >
    Loading languages …
  </Helper>;

  switch (localesFetchResult.type) {
    case 'idle': // intentional no break;
    case 'fetching': break;
    case "error":
      localesElement = <Helper
        inline
        level="error"
      >
        {__('Unexpected error occurred. Please contact system administrator.')}: {localesFetchResult.message}
      </Helper>
      break;
    case 'fetched':
      localesElement = <SelectInput
        openLabel=''
        emptyResultLabel="No result found"
        onChange={function noRefCheck() { }}
        placeholder="Please enter a value in the Select input"
        value={null}
      >
        {
          localesFetchResult.payload.map((locale) => {
            return (<SelectInput.Option
              key={locale.code}
              title={locale.label}
              value={locale.code}
            >
              <LocaleComponent
                code={locale.code}
                languageLabel={`${locale.language} (${locale.region})`}
              />
            </SelectInput.Option>)
          })}
      </SelectInput>
      break;

  }



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
          <Button>Save</Button>
        </PageHeader.Actions>
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
          <Field label={__('oro_config.form.config.group.loading_message.fields.enabler.label')}>
            <BooleanInput value={configFetchResult.payload.pim_ui___loading_message_enabled.value} yesLabel={__('pim_common.yes')} noLabel={__('pim_common.no')} readOnly={false} />
          </Field>
          <Field label={__('oro_config.form.config.group.loading_message.fields.messages.label')}>
            <TextAreaInput readOnly={false} value="foo" onChange={() => { }} />
          </Field>
        </Section>
        <Section>
          <SectionTitle>
            <SectionTitle.Title>{__('oro_config.form.config.group.localization.title')}</SectionTitle.Title>
          </SectionTitle>
          <Field label={__('oro_config.form.config.group.localization.fields.system_locale.label')}>
            {localesElement}
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
            <BooleanInput value={true} yesLabel={__('pim_common.yes')} noLabel={__('pim_common.no')} readOnly={false} />
          </Field>
        </Section>
      </PageContent>
    </>
  );
};

export { ConfigForm };
