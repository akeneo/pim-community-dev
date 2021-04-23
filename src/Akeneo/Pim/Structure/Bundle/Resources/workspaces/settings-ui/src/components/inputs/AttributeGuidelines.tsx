import React from 'react';
import {Field, TextAreaInput, LoaderIcon} from 'akeneo-design-system';
import {LocaleCode, LocaleSelector, Locale, useTranslate} from '@akeneo-pim-community/shared';
import {Guidelines} from '../../models';
import styled from 'styled-components';
const FetcherRegistry = require('pim/fetcher-registry');
const UserContext = require('pim/user-context');

const Header = styled.div`
  display: flex;
  justify-content: space-between;
  align-items: baseline;
`;

type AttributeGuidelinesProps = {
  defaultValue: Guidelines;
  onChange: (value: Guidelines) => void;
};

const AttributeGuidelines = ({defaultValue, onChange}: AttributeGuidelinesProps) => {
  const translate = useTranslate();
  const [guidelines, setGuidelines] = React.useState<Guidelines>(defaultValue);
  const [localeCode, setLocaleCode] = React.useState<LocaleCode>(UserContext.get('uiLocale'));
  const [locales, setLocales] = React.useState<Locale[]>();

  React.useEffect(() => {
    FetcherRegistry.initialize().then(async () => {
      const locales = await FetcherRegistry.getFetcher('ui-locale').fetchAll();
      setLocales(locales);
    });
  }, []);

  if (!locales) {
    return <LoaderIcon />;
  }

  const handleGuidelinesChange = (guideline: string) => {
    guidelines[localeCode] = guideline;
    setGuidelines({...guidelines});
    onChange({...guidelines});
  };

  return (
    <>
      <Header className="tabsection-title">
        {translate('pim_enrich.entity.attribute.property.guidelines')}
        <LocaleSelector
          value={localeCode}
          onChange={setLocaleCode}
          values={locales}
          completeValues={locales.map(locale => locale.code).filter(localeCode => !!guidelines[localeCode])}
        />
      </Header>
      <div className="AknFormContainer AknFormContainer--withPadding" data-drop-zone="content">
        <Field label={translate('pim_enrich.entity.attribute.property.guidelines')}>
          {locales
            .filter(locale => locale.code === localeCode)
            .map(locale => (
              <TextAreaInput
                key={locale.code}
                name={'guidelines'}
                type={'text'}
                onChange={handleGuidelinesChange}
                value={guidelines[localeCode]}
              />
            ))}
        </Field>
      </div>
    </>
  );
};

export {AttributeGuidelines};
