import React from 'react';
import {ChannelCode, LocaleCode} from '@akeneo-pim-community/shared';

type Props = {
  attributeCode: string;
  locale?: LocaleCode | null;
  scope?: ChannelCode | null;
  onChange: ({scope, locale}: {scope?: ChannelCode; locale?: LocaleCode}) => void;
};

const ScopeAndLocaleSelector: React.FC<Props> = ({attributeCode, locale = null, scope = null, onChange}) => {
  const handleChangeLocale = () => {
    onChange({scope: 'new_scope', locale: 'new_locale'});
  };

  return (
    <>
      ScopeAndLocaleSelectorMock
      <span>Attribute code: {attributeCode}</span>
      <span>locale selected : {locale}</span>
      <span>Channel selected : {scope}</span>
      <button onClick={handleChangeLocale}>Change values</button>
    </>
  );
};

export {ScopeAndLocaleSelector};
