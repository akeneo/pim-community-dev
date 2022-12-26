import React, {FC} from 'react';
import {useFeatureFlags} from '@akeneo-pim-community/shared';
import {getColor, Information, Link, ProductCategoryIllustration} from 'akeneo-design-system';
import styled from 'styled-components';

const Footer = styled.div`
  display: flex;
`;

const List = styled.ul`
  margin: 10px 0;
  padding-left: 8px;
  line-height: 18px;
  list-style-position: inside;
  color: ${getColor('grey', 140)};
`;

const Item = styled.li`
  ::marker {
    content: '\\2022\\00A0\\00A0';
  }
`;

const DiscoverEnrichedCategoriesInformationHelper: FC = () => {
  const featureFlags = useFeatureFlags();

  if (!featureFlags.isEnabled('enriched_category')) {
    return <></>;
  }

  return (
    <Information
      illustration={<ProductCategoryIllustration />}
      title="Discover Enriched Categories Early Access"
      data-testid="discover-enriched-categories-information-helper"
    >
      <List>
        <Item>Activate category enrichment through pre-configured templates</Item>
        <Item>Enrich Text, Text Area and Image attributes in categories</Item>
        <Item>Enrichment deactivation and custom template will be coming shortly</Item>
      </List>
      <Footer>
        <Link
          target="_blank"
          href="https://docs.google.com/forms/d/e/1FAIpQLSdrPJk35QxrtEc0gGwE39KJ42cMlEdH3TCygRfYTiG-zYx72A/viewform?usp=sf_link"
        >
          Tell us what you think
        </Link>
      </Footer>
    </Information>
  );
};

export {DiscoverEnrichedCategoriesInformationHelper};
