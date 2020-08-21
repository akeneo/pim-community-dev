import React from 'react';
import { Select2SimpleSyncWrapper, Select2Value } from '../Select2Wrapper';
import {
  Attribute,
  getAttributeLabel,
  LocaleCode,
  Scope,
  ScopeCode,
} from '../../models';
import { useTranslate } from '../../dependenciesTools/hooks';
import { Translate } from '../../dependenciesTools';
import { IndexedScopes } from '../../repositories/ScopeRepository';

const getScopeValidation = (
  attribute: Attribute | null,
  scopes: IndexedScopes,
  translate: Translate,
  currentCatalogLocale: LocaleCode
) => {
  const scopeValidation: any = {};
  if (attribute && attribute.scopable) {
    scopeValidation['required'] = translate(
      'pimee_catalog_rule.exceptions.required_scope',
      {
        attributeLabel: getAttributeLabel(attribute, currentCatalogLocale),
      }
    );
  }
  scopeValidation['validate'] = (scopeCode: any) => {
    if (attribute && attribute.scopable) {
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

  return scopeValidation;
};

type Props = {
  label?: string;
  hiddenLabel?: boolean;
  availableScopes: Scope[];
  currentCatalogLocale: LocaleCode;
  value?: ScopeCode;
  onChange?: (value: ScopeCode) => void;
  allowClear: boolean;
  disabled?: boolean;
  name?: string;
  validation?: { required?: string; validate?: (value: any) => string | true };
};

const ScopeSelector: React.FC<Props> = ({
  label,
  hiddenLabel = false,
  availableScopes,
  currentCatalogLocale,
  value,
  onChange,
  children,
  allowClear = false,
  disabled = false,
  validation,
  ...remainingProps
}) => {
  const translate = useTranslate();
  const getScopeLabel = (scope: Scope): string => {
    return scope.labels[currentCatalogLocale] || `[${scope.code}]`;
  };

  const scopeChoices = availableScopes.map((scope: Scope) => {
    return {
      id: scope.code,
      text: getScopeLabel(scope),
    };
  });

  if (value && !scopeChoices.some(scopeChoice => scopeChoice.id === value)) {
    scopeChoices.push({
      id: value,
      text: `[${value}]`,
    });
  }

  const handleChange = (value: Select2Value) => {
    if (onChange) {
      onChange(value as ScopeCode);
    }
  };

  return (
    <>
      <Select2SimpleSyncWrapper
        {...remainingProps}
        label={label || translate('pim_enrich.entity.channel.uppercase_label')}
        hiddenLabel={hiddenLabel}
        data={scopeChoices}
        hideSearch={true}
        placeholder={translate('pim_enrich.entity.channel.uppercase_label')}
        value={value || null}
        allowClear={allowClear}
        onChange={handleChange}
        disabled={disabled}
        validation={validation}
        dropdownCssClass='scope-dropdown'
      />
      {children}
    </>
  );
};

export { getScopeValidation, ScopeSelector };
