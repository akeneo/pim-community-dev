import React, {useEffect} from 'react';
import {
  PageContent,
  PageHeader,
  PimView,
  Section,
  useFetch,
  useRoute,
  useTranslate
} from '@akeneo-pim-community/shared';
import { BooleanInput, Breadcrumb, Field,FieldProps, Helper, Locale as LocaleItem, SectionTitle, SelectInput, TextAreaInput } from 'akeneo-design-system';
import {Locale} from 'models/types';


const ConfigForm = () => {
  const localeUrl = useRoute('pim_localization_locale_index');
  const [locales, doFetchLocales, fetchStatus, fetchLocaleError] = useFetch<Locale[]>(localeUrl);
  const __ = useTranslate();
  const systemHref = useRoute('pim_system_index');

  // TODO fetch only ones by session
  useEffect(() => {doFetchLocales()}, []);

  let localesElement: FieldProps['children'] = <Helper
      inline
      level="warning"
  >
    There is a warning.{'Fetching'}
  </Helper>;
  switch(fetchStatus) {
    case 'idle':
      // no break;
    case 'fetching':
      break;
    case 'fetched':
      localesElement = <SelectInput
          openLabel=''
          emptyResultLabel="No result found"
          onChange={function noRefCheck() { }}
          placeholder="Please enter a value in the Select input"
          value={null}
      >
        {locales!.map((locale) => {
          return (<SelectInput.Option
              key={locale.id}
              title={locale.label}
              value={locale.code}
          >
            <LocaleItem
                code={locale.code}
                languageLabel={`${locale.language} (${locale.region})`}
            />
          </SelectInput.Option>)
        })}
      </SelectInput>
          break;
    case "error":
      localesElement =  <Helper
          inline
          level="error"
      >
        {__('Unexpected error occurred. Please contact system administrator.')}: {fetchLocaleError}
      </Helper>
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
            <BooleanInput value={true} yesLabel={__('pim_common.yes')} noLabel={__('pim_common.no')} readOnly={false} />
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
