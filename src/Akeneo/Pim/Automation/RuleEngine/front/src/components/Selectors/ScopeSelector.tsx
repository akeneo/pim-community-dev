import React from 'react';
import { Select2SimpleSyncWrapper } from '../Select2Wrapper';
import { Scope } from '../../models';

type Props = {
  id: string;
  label: string;
  hiddenLabel?: boolean;
  availableScopes: Scope[];
  currentCatalogLocale: string;
  name: string;
};

const ScopeSelector: React.FC<Props> = ({
  id,
  label,
  hiddenLabel = false,
  availableScopes,
  currentCatalogLocale,
  name,
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
      name={name}
      placeholder={'Channel'}
    />
  );
};

export { ScopeSelector };
