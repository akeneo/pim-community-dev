import * as React from 'react';
import Completeness from 'akeneoassetmanager/domain/model/asset/completeness';
import __ from 'akeneoassetmanager/tools/translator';

const memo = (React as any).memo;

export const getLabel = (value: number, expanded: boolean) => {
  return `${expanded ? __('pim_asset_manager.asset.completeness.label') + ': ' : ''}${value}%`;
};

export const getCompletenessClass = (completeness: Completeness, expanded: boolean) => {
  if (!completeness.hasCompleteAttribute()) {
    return `AknBadge AknBadge--${expanded ? 'big' : 'medium'} AknBadge--invalid`;
  } else if (completeness.isComplete()) {
    return `AknBadge AknBadge--${expanded ? 'big' : 'medium'} AknBadge--success`;
  } else {
    return `AknBadge AknBadge--${expanded ? 'big' : 'medium'} AknBadge--warning`;
  }
};

export const getTranslationKey = (completeness: Completeness) => {
  const keyBase = 'pim_asset_manager.asset.grid.completeness';

  if (!completeness.hasCompleteAttribute()) {
    return `${keyBase}.title_non_complete`;
  } else if (completeness.isComplete()) {
    return `${keyBase}.title_complete`;
  } else {
    return `${keyBase}.title_ongoing`;
  }
};

const CompletenessLabel = memo(({completeness, expanded = true}: {completeness: Completeness; expanded: boolean}) => {
  if (!completeness.hasRequiredAttribute()) {
    return <span title={__('pim_asset_manager.asset.grid.completeness.title_no_required')}>-</span>;
  }

  return (
    <span
      title={__(getTranslationKey(completeness), {
        complete: completeness.getCompleteAttributeCount(),
        required: completeness.getRequiredAttributeCount(),
      })}
      className={getCompletenessClass(completeness, expanded)}
    >
      {getLabel(completeness.getRatio(), expanded)}
    </span>
  );
});

export default CompletenessLabel;
