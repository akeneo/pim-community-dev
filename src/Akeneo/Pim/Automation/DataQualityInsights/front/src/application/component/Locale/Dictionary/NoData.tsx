import React from 'react';
import {EmptyDataPlaceholder} from './EmptyDataPlaceholder';
import {LocaleIllustration} from 'akeneo-design-system';

const NoData = () => {
  return (
    <EmptyDataPlaceholder
      illustration={<LocaleIllustration />}
      title={'akeneo_data_quality_insights.dictionary.no_data.title'}
      subtitle={'akeneo_data_quality_insights.dictionary.no_data.subtitle'}
    />
  );
};

export {NoData};
