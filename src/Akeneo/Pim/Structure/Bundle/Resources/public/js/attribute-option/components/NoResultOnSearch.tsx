import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {getColor, NoResultsIllustration} from 'akeneo-design-system';
import styled from 'styled-components';

const NoResultOnSearch = () => {
  const translate = useTranslate();

  return (
    <NoResultSection>
      <NoResultsIllustration size={128} />
      <NoResultTitle>
        {translate('pim_enrich.entity.attribute_option.module.edit.search.no_result.title')}
      </NoResultTitle>
    </NoResultSection>
  );
};

const NoResultSection = styled.div`
  text-align: center;
  margin-top: 42px;
`;

const NoResultTitle = styled.div`
  color: ${getColor('grey', 120)};
  text-align: center;
`;

export default NoResultOnSearch;
