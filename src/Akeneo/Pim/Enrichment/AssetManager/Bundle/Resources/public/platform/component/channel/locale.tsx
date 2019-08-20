import * as React from 'react';
import {LocaleCode, Locale} from 'akeneopimenrichmentassetmanager/platform/model/channel/locale';
import styled from 'styled-components';
import {Label} from 'akeneopimenrichmentassetmanager/platform/component/common/label';
import Flag from 'akeneoassetmanager/tools/component/flag';

const LocaleLabelView = styled(Label)`
  margin-left: 10px;
`;

export const LocaleLabel = ({localeCode, locales}: {localeCode: LocaleCode, locales: Locale[]}) => {
  const locale = getLocale(localeCode, locales);

  if (undefined === locale) {
    return <LocaleLabelView>{localeCode}</LocaleLabelView>
  }

  return (
    <LocaleLabelView>
      <Flag locale={locale} displayLanguage={true} />
    </LocaleLabelView>
  )
}

const getLocale = (localeCode: LocaleCode, locales: Locale[]) => {
  return locales.find((locale: Locale) => locale.code === localeCode)
}
