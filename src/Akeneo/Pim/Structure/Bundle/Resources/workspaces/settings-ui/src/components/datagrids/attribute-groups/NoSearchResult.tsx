import React from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import styled from 'styled-components';

const NoSearchResult = () => {
  const translate = useTranslate();

  const Container = styled.div`
    margin-top: 120px;
    text-align: center;
  `;

  const NoResultMessage = styled.div`
    font-size: 28px;
    color: #11324d;
    margin-top: 5px;
  `;

  const TryAgainMessage = styled.div`
    font-size: 17px;
    margin-top: 15px;
  `;

  return (
    <Container>
      <img src="/bundles/pimui/images/illustrations/Attribute-groups.svg" alt="" />
      <NoResultMessage>{translate('pim_enrich.entity.attribute_group.grid.no_search_result')}</NoResultMessage>
      <TryAgainMessage>{translate('pim_datagrid.no_results_subtitle')}</TryAgainMessage>
    </Container>
  );
};

export {NoSearchResult};
