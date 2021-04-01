import React from 'react';
import {SectionTitle} from 'akeneo-design-system';
import {useTranslate, useUserContext} from '@akeneo-pim-community/legacy-bridge';
import styled from 'styled-components';
import {useDashboardCompleteness} from '../../hooks';
import {ChannelsLocalesCompleteness, ChannelsLocalesCompletenessRatios} from "@akeneo-pim-community/enrichment";

const CompletenessWidget = () => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const data: ChannelsLocalesCompletenessRatios | null = useDashboardCompleteness(userContext.get('catalogLocale'));

  return (
    <>
      <SectionTitle>
        <SectionTitle.Title>{translate('pim_dashboard.widget.completeness.title')}</SectionTitle.Title>
      </SectionTitle>

      <Container>{data != null && <ChannelsLocalesCompleteness channelsLocalesRatios={data} />}</Container>
    </>
  );
};

const Container = styled.div`
  margin: 30px 0 40px 0;
`;

export {CompletenessWidget};
