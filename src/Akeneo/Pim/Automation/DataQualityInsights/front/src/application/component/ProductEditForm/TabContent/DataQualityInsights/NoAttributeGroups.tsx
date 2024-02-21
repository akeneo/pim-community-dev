import React from 'react';
import styled from 'styled-components';

const translate = require('oro/translator');

const Message = styled.div`
  text-align: center;
  width: 100%;
  margin-top: 100px;
`;

const Title = styled.div`
  font-size: ${({theme}) => theme.fontSize.title};
  color: ${({theme}) => theme.color.grey140};
  margin-top: 5px;
`;

const Subtitle = styled.div`
  font-size: ${({theme}) => theme.fontSize.big};
  margin-top: 10px;
`;

const HelpCenterLink = styled.a`
  font-size: ${({theme}) => theme.fontSize.big};
  color: ${({theme}) => theme.color.purple100};
  cursor: pointer;
  margin-top: 5px;
  text-decoration: underline;
`;

const NoAttributeGroups = () => {
  return (
    <Message>
      <img src="bundles/akeneodataqualityinsights/images/QualityScore.svg" />
      <Title>{translate('akeneo_data_quality_insights.product_evaluation.messages.no_attribute_groups.title')}</Title>
      <Subtitle>
        {translate('akeneo_data_quality_insights.product_evaluation.messages.no_attribute_groups.subtitle')}
      </Subtitle>
      <HelpCenterLink href="https://help.akeneo.com/pim/serenity/articles/understand-data-quality.html" target="_blank">
        {translate('akeneo_data_quality_insights.product_evaluation.messages.no_attribute_groups.help_center_link')}
      </HelpCenterLink>
    </Message>
  );
};

export {NoAttributeGroups};
