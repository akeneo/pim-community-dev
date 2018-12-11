import * as React from 'react';
import Completeness from 'akeneoreferenceentity/domain/model/record/completeness';
import __ from 'akeneoreferenceentity/tools/translator';

const memo = (React as any).memo;

const getLabel = (value: number, expanded: boolean) => {
  return `${expanded ? __('pim_reference_entity.record.completeness.label') + ': ' : ''}${value}%`;
};

const getCompletenessClass = (completeness: Completeness, expanded: boolean) => {
  if (!completeness.hasCompleteAttribute()) {
    return `AknBadge AknBadge--${expanded ? 'big' : 'medium'} AknBadge--invalid`;
  } else if (completeness.isComplete()) {
    return `AknBadge AknBadge--${expanded ? 'big' : 'medium'} AknBadge--success`;
  } else {
    return `AknBadge AknBadge--${expanded ? 'big' : 'medium'} AknBadge--warning`;
  }
};

const getTranslationKey = (completeness: Completeness) => {
  if (!completeness.hasCompleteAttribute()) {
    return 'title_non_complete';
  } else if (completeness.isComplete()) {
    return 'title_complete';
  } else {
    return 'title_ongoing';
  }
};

const CompletenessLabel = memo(({completeness, expanded = true}: {completeness: Completeness; expanded: boolean}) => {
  if (!completeness.hasRequiredAttribute()) {
    return <span title={__('pim_reference_entity.record.grid.completeness.title_no_required')}>-</span>;
  }

  return (
    <span
      title={__(`pim_reference_entity.record.grid.completeness.${getTranslationKey(completeness)}`, {
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
