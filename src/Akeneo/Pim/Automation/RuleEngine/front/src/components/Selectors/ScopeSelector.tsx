import React from 'react';
import { Select2SimpleSyncWrapper } from '../Select2Wrapper';
import { Scope } from '../../models';

type Props = {
  id: string;
  label: string;
  hiddenLabel?: boolean;
  availableScopes: Scope[];
  currentCatalogLocale: string;
  value: string;
  onChange: (value: string) => void;
};

const ScopeSelector: React.FC<Props> = ({
  id,
  label,
  hiddenLabel = false,
  availableScopes,
  currentCatalogLocale,
  value,
  onChange,
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

  return (
    <Select2SimpleSyncWrapper
      id={id}
      label={label}
      hiddenLabel={hiddenLabel}
      data={scopeChoices}
      hideSearch={true}
      placeholder={'Channel'}
      value={value}
      onValueChange={(value) => onChange(value as string) }
    />
  );
};

export { ScopeSelector };
