import React from 'react';
import { Field, TextareaInput, LoaderIcon } from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import { LocaleCode, LocaleSelector, Locale } from '@akeneo-pim-community/shared';
import {Descriptions} from '../../models';
import styled from "styled-components";
const FetcherRegistry = require('pim/fetcher-registry');

const Header = styled.div`
  display: flex;
  justify-content: space-between;
  align-items: baseline;
`

type AttributeDescriptionsProps = {
  defaultValue: Descriptions;
  onChange: (value: Descriptions) => void;
};

const AttributeDescriptions = ({defaultValue, onChange}: AttributeDescriptionsProps) => {
  const translate = useTranslate();
  const [descriptions, setDescriptions] = React.useState<Descriptions>(defaultValue);
  const [localeCode, setLocaleCode] = React.useState<LocaleCode>('en_US');
  const [locales, setLocales] = React.useState<Locale[]>();

  React.useEffect(() => {
    FetcherRegistry.initialize().then(async () => {
      const locales = await FetcherRegistry.getFetcher('locale').fetchActivated();
      setLocales(locales);
    });
  }, []);

  if (!locales) {
    return <LoaderIcon/>;
  }

  const handleDescriptionsChange = (description: string) => {
    descriptions[localeCode] = description;
    setDescriptions({...descriptions});
    onChange({...descriptions});
  };

  return (
    <>
      <Header className="tabsection-title">
        {translate('pim_enrich.entity.attribute.property.descriptions')}
        <LocaleSelector
          value={localeCode}
          onChange={setLocaleCode}
          values={locales}
          completeValues={locales.map(locale => locale.code).filter(localeCode => !!descriptions[localeCode])}
        />
      </Header>
      <div className="AknFormContainer AknFormContainer--withPadding" data-drop-zone="content">
        <Field label={translate('pim_enrich.entity.attribute.property.descriptions')}>
          {locales.filter(locale => locale.code === localeCode).map((locale) =>
            <TextareaInput
              key={locale.code}
              name={'descriptions'}
              type={'text'}
              onChange={handleDescriptionsChange}
              value={descriptions[localeCode]}
            />
          )}
        </Field>
      </div>
    </>
  );
};

export {AttributeDescriptions};
