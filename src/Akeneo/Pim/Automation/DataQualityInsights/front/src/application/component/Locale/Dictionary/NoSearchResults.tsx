import React from 'react';
import {EmptyDataPlaceholder} from './EmptyDataPlaceholder';
import {NoResultsIllustration} from 'akeneo-design-system';

const NoSearchResults = () => {
  return (
    <EmptyDataPlaceholder
      illustration={<NoResultsIllustration />}
      title={'akeneo_data_quality_insights.dictionary.no_search_results.title'}
      subtitle={'akeneo_data_quality_insights.dictionary.no_search_results.subtitle'}
    />
  );
};

export {NoSearchResults};
