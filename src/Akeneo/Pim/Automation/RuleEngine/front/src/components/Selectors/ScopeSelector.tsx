import React from 'react';
import { Select2SimpleSyncWrapper } from '../Select2Wrapper';
import { Scope } from '../../models';

type Props = {
  id: string;
  label: string;
  hiddenLabel?: boolean;
  currentScopeCode: string;
  availableScopes: Scope[];
  onSelectorChange: (value: string) => void;
  currentCatalogLocale: string;
};

const ScopeSelector: React.FC<Props> = ({
  id,
  label,
  hiddenLabel = false,
  currentScopeCode,
  availableScopes,
  onSelectorChange,
  currentCatalogLocale,
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
      onChange={(value: string) => {
        onSelectorChange(value);
      }}
      value={currentScopeCode}
      data={scopeChoices}
      hideSearch={true}
    />
  );
};

export { ScopeSelector };
