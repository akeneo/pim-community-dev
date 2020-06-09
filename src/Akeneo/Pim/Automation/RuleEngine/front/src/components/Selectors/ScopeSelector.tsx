import React from 'react';
import { Select2SimpleSyncWrapper } from '../Select2Wrapper';
import { LocaleCode, Scope, ScopeCode } from '../../models';
import { Translate } from '../../dependenciesTools';

type Props = {
  id: string;
  label: string;
  hiddenLabel?: boolean;
  availableScopes: Scope[];
  currentCatalogLocale: LocaleCode;
  value: ScopeCode;
  onChange: (value: ScopeCode) => void;
  translate: Translate;
  allowClear: boolean;
  disabled?: boolean;
};

const ScopeSelector: React.FC<Props> = ({
  id,
  label,
  hiddenLabel = false,
  availableScopes,
  currentCatalogLocale,
  value,
  onChange,
  translate,
  children,
  allowClear = false,
  disabled = false,
}) => {
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

  return (
    <>
      <Select2SimpleSyncWrapper
        id={id}
        label={label}
        hiddenLabel={hiddenLabel}
        data={scopeChoices}
        hideSearch={true}
        placeholder={translate('pim_enrich.entity.channel.uppercase_label')}
        value={value}
        allowClear={allowClear}
        onValueChange={value => onChange(value as ScopeCode)}
        disabled={disabled}
      />
      {children}
    </>
  );
};

export { ScopeSelector };
