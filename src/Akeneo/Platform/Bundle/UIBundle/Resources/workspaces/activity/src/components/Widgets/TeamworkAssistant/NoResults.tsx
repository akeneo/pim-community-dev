import React from 'react';
import {GroupsIllustration, getColor} from 'akeneo-design-system';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';

const NoResults = () => {
  const translate = useTranslate();

  return (
    <Container>
      <GroupsIllustration size={128} />
      <NoResultsLabel>{translate('teamwork_assistant.widget.no_search_results')}</NoResultsLabel>
    </Container>
  );
};

const Container = styled.div`
  text-align: center;
  line-height: normal;
  padding-bottom: 20px;
`;

const NoResultsLabel = styled.div`
  color: ${getColor('grey', 140)};
`;

export {NoResults};
