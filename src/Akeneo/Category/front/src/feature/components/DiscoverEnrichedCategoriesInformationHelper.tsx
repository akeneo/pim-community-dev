import React, {FC} from 'react';
import {useFeatureFlags} from '@akeneo-pim-community/shared';

const DiscoverEnrichedCategoriesInformationHelper: FC = () => {
  const featureFlags = useFeatureFlags();
  if (!featureFlags.isEnabled('enriched_category')) {
    return <></>;
  }
  return <div data-testid="discover-enriched-categories-information-helper">Discover Enriched Categories</div>;
};

export {DiscoverEnrichedCategoriesInformationHelper};
