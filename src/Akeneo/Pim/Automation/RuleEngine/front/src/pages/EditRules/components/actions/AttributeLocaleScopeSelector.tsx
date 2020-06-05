import React from 'react';
import {
  Attribute,
  AttributeCode,
  Locale,
  LocaleCode,
  ScopeCode,
} from '../../../../models';
import { AttributeSelector } from '../../../../components/Selectors/AttributeSelector';
import { ScopeSelector } from '../../../../components/Selectors/ScopeSelector';
import { LocaleSelector } from '../../../../components/Selectors/LocaleSelector';
import {
  useBackboneRouter,
  useTranslate,
  useUserCatalogLocale,
} from '../../../../dependenciesTools/hooks';
import { getAttributeByIdentifier } from '../../../../repositories/AttributeRepository';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { useFormContext } from 'react-hook-form';
import { IndexedScopes } from '../../../../repositories/ScopeRepository';
import styled from 'styled-components';

const SelectorBlock = styled.div`
  margin-top: 15px;
`;

type Props = {
  attributeCode: AttributeCode | null;
  attributeFormName: string;
  localeCode: LocaleCode | null;
  scopeCode: ScopeCode | null;
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
  locales,
  scopes,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const currentCatalogLocale = useUserCatalogLocale();
  const { watch, setValue, register } = useFormContext();
  const [attribute, setAttribute] = React.useState<Attribute | null>(null);
  const [attributeIsChanged, setAttributeIsChanged] = React.useState<boolean>(
    false
  );

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

  const localeValidate = (localeCode: any) => {
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

  const scopeValidate = (scopeCode: any) => {
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
  formValidations[scopeFormName] = { validate: scopeValidate };
  formValidations[localeFormName] = { validate: localeValidate };
  useValueInitialization('', formValues, formValidations, [
    attributeCode,
    scopeCode,
    localeCode,
  ]);

  const setAttributeFormValue = (value: AttributeCode | null) => {
    setAttributeIsChanged(true);
    setValue(attributeFormName, value, true);
    refreshAttribute(value);
  };

  const setLocaleFormValue = (value: LocaleCode | null) => {
    setValue(localeFormName, value, true);
  };

  const setScopeFormValue = (value: ScopeCode | null) => {
    setValue(scopeFormName, value, true);
    if (
      !getAvailableLocales()
        .map(locale => locale.code)
        .includes(getLocaleFormValue())
    ) {
      setLocaleFormValue(null);
    }
  };

  React.useEffect(() => {
    // Refresh the validation with the new attribute
    register({ name: localeFormName }, { validate: localeValidate });
    register({ name: scopeFormName }, { validate: scopeValidate });

    if (attributeIsChanged && attribute) {
      if (!attribute.localizable) {
        setLocaleFormValue(null);
      }
      if (!attribute.scopable) {
        setScopeFormValue(null);
      }
    }
  }, [attribute]);

  React.useEffect(() => {
    refreshAttribute(attributeCode);
  }, []);

  return (
    <div className={'AknFormContainer'}>
      <SelectorBlock>
        <AttributeSelector
          id={attributeId}
          label={attributeLabel}
          currentCatalogLocale={currentCatalogLocale}
          value={getAttributeFormValue()}
          onChange={setAttributeFormValue}
          placeholder={attributePlaceholder}
        />
      </SelectorBlock>
      {(attribute?.scopable || (!attributeIsChanged && scopeCode)) && (
        <SelectorBlock>
          <ScopeSelector
            id={scopeId}
            label={
              scopeLabel ||
              `${translate('Channel')} ${translate(
                'pim_common.required_label'
              )}`
            }
            availableScopes={Object.values(scopes)}
            currentCatalogLocale={currentCatalogLocale}
            value={getScopeFormValue()}
            onChange={setScopeFormValue}
            translate={translate}
            allowClear={!attribute?.scopable}
          />
        </SelectorBlock>
      )}
      {(attribute?.localizable || (!attributeIsChanged && localeCode)) && (
        <SelectorBlock>
          <LocaleSelector
            id={localeId}
            label={
              localeLabel ||
              `${translate('Locale')} ${translate('pim_common.required_label')}`
            }
            availableLocales={locales}
            value={getLocaleFormValue()}
            onChange={setLocaleFormValue}
            translate={translate}
            allowClear={!attribute?.localizable}
          />
        </SelectorBlock>
      )}
    </div>
  );
};
