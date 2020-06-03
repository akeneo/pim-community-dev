import React from 'react';
import {
  Attribute,
  AttributeCode,
  Locale,
  LocaleCode,
  ScopeCode,
} from '../../../../models';
import { Translate } from '../../../../dependenciesTools';
import { AttributeSelector } from '../../../../components/Selectors/AttributeSelector';
import { ScopeSelector } from '../../../../components/Selectors/ScopeSelector';
import { LocaleSelector } from '../../../../components/Selectors/LocaleSelector';
import { useBackboneRouter } from '../../../../dependenciesTools/hooks';
import { getAttributeByIdentifier } from '../../../../repositories/AttributeRepository';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { useFormContext } from 'react-hook-form';
import { IndexedScopes } from '../../../../repositories/ScopeRepository';

type Props = {
  attributeCode: AttributeCode | null;
  attributeFormName: string;
  localeCode: LocaleCode | null;
  scopeCode: ScopeCode | null;
  currentCatalogLocale: string;
  translate: Translate;
  attributeId: string;
  attributeLabel: string;
  attributePlaceholder: string;
  scopeId: string;
  scopeFormName: string;
  scopeLabel?: string;
  localeId: string;
  localeLabel?: string;
  localeFormName: string;
  locales: Locale[];
  scopes: IndexedScopes;
};

export const AttributeLocaleScopeSelector: React.FC<Props> = ({
  attributeCode,
  attributeId,
  attributeLabel,
  attributePlaceholder,
  attributeFormName,
  scopeCode,
  localeCode,
  scopeId,
  scopeLabel,
  scopeFormName,
  localeId,
  localeLabel,
  localeFormName,
  currentCatalogLocale,
  translate,
  locales,
  scopes,
}) => {
  const router = useBackboneRouter();
  const { watch, setValue, triggerValidation } = useFormContext();
  const [attribute, setAttribute] = React.useState<Attribute | null>(null);

  const refreshAttribute: (
    attributeCode: AttributeCode | null
  ) => void = async (attributeCode: AttributeCode | null) => {
    setAttribute(
      attributeCode !== null
        ? await getAttributeByIdentifier(attributeCode, router)
        : null
    );
  };

  const getAttributeFormValue: () => AttributeCode = () =>
    watch(attributeFormName);
  const getScopeFormValue: () => ScopeCode = () => watch(scopeFormName);
  const getLocaleFormValue: () => LocaleCode = () => watch(localeFormName);

  const getAvailableLocales = (): Locale[] => {
    if (!attribute?.scopable) {
      return locales;
    }

    const scopeCode = getScopeFormValue();
    if (scopeCode && scopes[scopeCode]) {
      return scopes[scopeCode].locales;
    }

    return [];
  };

  const localeValidation: any = {};
  localeValidation['validate'] = (localeCode: any) => {
    if (!attribute) {
      return true;
    }

    if (attribute.localizable) {
      if (!localeCode) {
        return translate('pimee_catalog_rule.exceptions.required_locale');
      }

      if (!locales.some(locale => locale.code === localeCode)) {
        return translate(
          'pimee_catalog_rule.exceptions.unknown_or_inactive_locale',
          { localeCode }
        );
      }
      if (!getAvailableLocales().some(locale => locale.code === localeCode)) {
        return attribute.scopable
          ? translate('pimee_catalog_rule.exceptions.unbound_locale', {
              localeCode,
              scopeCode: getScopeFormValue(),
            })
          : translate(
              'pimee_catalog_rule.exceptions.unknown_or_inactive_locale',
              { localeCode }
            );
      }
    } else {
      if (localeCode) {
        return translate(
          'pimee_catalog_rule.exceptions.locale_on_unlocalizable_attribute'
        );
      }
    }
    return true;
  };

  const scopeValidation: any = {};
  scopeValidation['validate'] = (scopeCode: any) => {
    if (!attribute) {
      return true;
    }

    if (attribute.scopable) {
      if (!scopeCode) {
        return translate('pimee_catalog_rule.exceptions.required_scope');
      }
      if (!scopes[scopeCode]) {
        return translate('pimee_catalog_rule.exceptions.unknown_scope', {
          scopeCode,
        });
      }
    } else {
      if (scopeCode) {
        return translate(
          'pimee_catalog_rule.exceptions.scope_on_unscopable_attribute'
        );
      }
    }
    return true;
  };

  const formValues: { [key: string]: any } = {};
  formValues[attributeFormName] = attributeCode;
  formValues[scopeFormName] = scopeCode;
  formValues[localeFormName] = localeCode;
  const formValidations: { [key: string]: any } = {};
  formValidations[attributeFormName] = {
    required: translate('pimee_catalog_rule.exceptions.required_attribute'),
  };
  formValidations[scopeFormName] = scopeValidation;
  formValidations[localeFormName] = localeValidation;
  useValueInitialization('', formValues, formValidations);

  const setAttributeFormValue = (value: AttributeCode | null) => {
    setValue(attributeFormName, value);
    triggerValidation(attributeFormName);
    refreshAttribute(value);
  };

  const setLocaleFormValue = (value: LocaleCode | null) => {
    setValue(localeFormName, value);
    triggerValidation(localeFormName);
  };

  const setScopeFormValue = (value: ScopeCode) => {
    setValue(scopeFormName, value);
    triggerValidation(scopeFormName);
    if (
      !getAvailableLocales()
        .map(locale => locale.code)
        .includes(getLocaleFormValue())
    ) {
      setLocaleFormValue(null);
    }
  };

  React.useEffect(() => {
    refreshAttribute(attributeCode);
  }, [attributeCode]);

  return (
    <div className={'AknFormContainer'}>
      <AttributeSelector
        id={attributeId}
        label={attributeLabel}
        currentCatalogLocale={currentCatalogLocale}
        value={getAttributeFormValue()}
        onChange={setAttributeFormValue}
        placeholder={attributePlaceholder}
      />

      {(attribute?.scopable || scopeCode) && (
        <ScopeSelector
          id={scopeId}
          label={scopeLabel || translate('Channel')}
          availableScopes={Object.values(scopes)}
          currentCatalogLocale={currentCatalogLocale}
          value={getScopeFormValue()}
          onChange={setScopeFormValue}
          translate={translate}
          allowClear={!(attribute && attribute.scopable)}
        />
      )}
      {(attribute?.localizable || localeCode) && (
        <LocaleSelector
          id={localeId}
          label={localeLabel || translate('Locale')}
          availableLocales={locales}
          value={getLocaleFormValue()}
          onChange={setLocaleFormValue}
          translate={translate}
          allowClear={!(attribute && attribute.localizable)}
        />
      )}
    </div>
  );
};
