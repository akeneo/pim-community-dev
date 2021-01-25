import React from 'react';
import Completeness from 'akeneoassetmanager/domain/model/asset/completeness';
import {Badge} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const getLevel = (completeness: Completeness) => {
  if (!completeness.hasCompleteAttribute()) {
    return 'danger';
  } else if (completeness.isComplete()) {
    return 'primary';
  } else {
    return 'warning';
  }
};

const getTranslationKey = (completeness: Completeness) => {
  const keyBase = 'pim_asset_manager.asset.grid.completeness';

  if (!completeness.hasCompleteAttribute()) {
    return `${keyBase}.title_non_complete`;
  } else if (completeness.isComplete()) {
    return `${keyBase}.title_complete`;
  } else {
    return `${keyBase}.title_ongoing`;
  }
};

type CompletenessBadgeProps = {completeness: Completeness};

const CompletenessBadge = ({completeness}: CompletenessBadgeProps) => {
  const translate = useTranslate();
  if (!completeness.hasRequiredAttribute()) {
    return <span title={translate('pim_asset_manager.asset.grid.completeness.title_no_required')}>-</span>;
  }

  return (
    <Badge
      level={getLevel(completeness)}
      title={translate(getTranslationKey(completeness), {
        complete: completeness.getCompleteAttributeCount(),
        required: completeness.getRequiredAttributeCount(),
      })}
    >
      {completeness.getRatio()}%
    </Badge>
  );
};

export {CompletenessBadge};
