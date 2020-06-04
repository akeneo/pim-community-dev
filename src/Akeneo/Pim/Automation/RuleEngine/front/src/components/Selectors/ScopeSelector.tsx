import React from 'react';
import { Select2SimpleSyncWrapper, Select2Value } from '../Select2Wrapper';
import { LocaleCode, Scope, ScopeCode } from '../../models';
import { useTranslate } from "../../dependenciesTools/hooks";

type Props = {
  label?: string;
  hiddenLabel?: boolean;
  availableScopes: Scope[];
  currentCatalogLocale: LocaleCode;
  value?: ScopeCode;
  onChange?: (value: ScopeCode) => void;
  allowClear: boolean;
  disabled?: boolean;
  name: string;
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
  name,
  validation,
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
      onChange(value as ScopeCode)
    }
  }

  return (
    <>
      <Select2SimpleSyncWrapper
        label={label || translate('pim_enrich.entity.channel.uppercase_label')}
        hiddenLabel={hiddenLabel}
        data={scopeChoices}
        hideSearch={true}
        placeholder={translate('pim_enrich.entity.channel.uppercase_label')}
        value={value || null}
        allowClear={allowClear}
        onChange={handleChange}
        disabled={disabled}
        name={name}
        validation={validation}
      />
      {children}
    </>
  );
};

export { ScopeSelector };
