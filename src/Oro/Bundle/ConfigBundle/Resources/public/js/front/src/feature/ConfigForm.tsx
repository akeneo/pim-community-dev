import React from 'react';
import { PageContent, PageHeader, PimView, Section, useRoute, useTranslate } from '@akeneo-pim-community/shared';
import { BooleanInput, Breadcrumb, Field, Helper, Locale, SectionTitle, SelectInput, TextAreaInput } from 'akeneo-design-system';


const ConfigForm = () => {
  const __ = useTranslate();
  const systemHref = useRoute('pim_system_index');

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
            <SelectInput
              openLabel=''
              emptyResultLabel="No result found"
              onChange={function noRefCheck() { }}
              placeholder="Please enter a value in the Select input"
              value={null}
            >
              {/* todo fetch locales cf src/Oro/Bundle/ConfigBundle/Resources/public/js/form/system/tab/localization.js */}
              <SelectInput.Option
                title="English (United States)"
                value="en_US"
              >
                <Locale
                  code="en_US"
                  languageLabel="English"
                />
              </SelectInput.Option>
              <SelectInput.Option
                title="French (France)"
                value="fr_FR"
              >
                <Locale
                  code="fr_FR"
                  languageLabel="French"
                />
              </SelectInput.Option>
              <SelectInput.Option
                title="German (Germany)"
                value="de_DE"
              >
                <Locale
                  code="de_DE"
                  languageLabel="German"
                />
              </SelectInput.Option>
            </SelectInput>
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
