import React from 'react';
import {
  Attribute,
  AttributeCode,
  Locale,
  LocaleCode,
  Scope,
  ScopeCode,
} from '../../../../models';
import { Translate } from '../../../../dependenciesTools';
import { AttributeSelector } from '../../../../components/Selectors/AttributeSelector';
import { LocaleColumn, ScopeColumn } from '../conditions/style';
import { ScopeSelector } from '../../../../components/Selectors/ScopeSelector';
import { LocaleSelector } from '../../../../components/Selectors/LocaleSelector';

type Props = {
  attributeCode: AttributeCode | null;
  localeCode?: LocaleCode;
  scopeCode?: ScopeCode;
  onAttributeChange: (attributeCode: AttributeCode) => void;
  onLocaleChange: (localeCode: LocaleCode) => void;
  onScopeChange: (scopeCode: ScopeCode) => void;
  currentCatalogLocale: string;
  translate: Translate;
  attributeId: string;
  attributeLabel: string;
  attributePlaceholder: string;
  scopeId: string;
  scopeLabel: string;
  scopePlaceholder: string;
  localeId: string;
  localeLabel: string;
  localePlaceholder: string;
};

export const AttributeLocaleScopeSelector: React.FC<Props> = ({
  attributeCode,
  onAttributeChange,
  attributeId,
  attributeLabel,
  attributePlaceholder,
  scopeCode,
  localeCode,
  scopeId,
  scopeLabel,
  onScopeChange,
  localeId,
  localeLabel,
  onLocaleChange,
  currentCatalogLocale,
  translate,
}) => {
  const [attribute, setAttribute] = React.useState<Attribute | null>(null);
  console.log(setAttribute);

  // @todo
  const scopes: Scope[] = [];
  const locales: Locale[] = [];

  return (
    <div className={'AknFormContainer'}>
      <AttributeSelector
        id={attributeId}
        label={attributeLabel}
        currentCatalogLocale={currentCatalogLocale}
        value={attributeCode}
        onChange={onAttributeChange}
        placeholder={attributePlaceholder}
      />

      <ScopeColumn>
        {(attribute?.scopable || scopeCode) && (
          <ScopeSelector
            id={scopeId}
            label={scopeLabel}
            availableScopes={scopes}
            currentCatalogLocale={currentCatalogLocale}
            value={scopeCode as ScopeCode}
            onChange={onScopeChange}
            translate={translate}
            allowClear={!(attribute && attribute.scopable)}
          />
        )}
      </ScopeColumn>
      <LocaleColumn>
        {(attribute?.localizable || localeCode) && (
          <LocaleSelector
            id={localeId}
            label={localeLabel}
            availableLocales={locales}
            value={localeCode as LocaleCode}
            onChange={onLocaleChange}
            translate={translate}
            allowClear={!(attribute && attribute.localizable)}
          />
        )}
      </LocaleColumn>
    </div>
  );
};
