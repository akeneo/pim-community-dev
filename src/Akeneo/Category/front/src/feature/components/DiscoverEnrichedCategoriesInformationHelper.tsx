import React, {FC} from 'react';
import {useFeatureFlags, useTranslate} from '@akeneo-pim-community/shared';
import {getColor, Information, Link, ProductCategoryIllustration} from 'akeneo-design-system';
import styled from 'styled-components';

const Container = styled.div`
  margin-bottom: 20px;
`;

const Footer = styled.div`
  display: flex;
`;

const List = styled.ul`
  margin: 10px 0;
  padding: 0 0 0 20px;
  line-height: 18px;
  list-style: none;
  color: ${getColor('grey', 140)};
`;

const Item = styled.li`
  ::marker {
    content: '\\2022\\00A0\\00A0';
  }
`;

const surveyLink =
  'https://docs.google.com/forms/d/e/1FAIpQLSdrPJk35QxrtEc0gGwE39KJ42cMlEdH3TCygRfYTiG-zYx72A/viewform?usp=sf_link';

const DiscoverEnrichedCategoriesInformationHelper: FC = () => {
  const featureFlags = useFeatureFlags();
  const translate = useTranslate();

  if (!featureFlags.isEnabled('enriched_category')) {
    return <></>;
  }

  return (
    <Container data-testid="discover-enriched-categories-information-helper">
      <Information
        illustration={<ProductCategoryIllustration />}
        title={translate('akeneo.category.discover_enriched_categories_information.title')}
      >
        <List>
          <Item>{translate('akeneo.category.discover_enriched_categories_information.content.activate_template')}</Item>
          <Item>
            {translate('akeneo.category.discover_enriched_categories_information.content.enrich_attribute_values')}
          </Item>
          <Item>
            {translate('akeneo.category.discover_enriched_categories_information.content.deactivate_template')}
          </Item>
        </List>
        <Footer>
          <Link href={surveyLink} target="_blank">
            {translate('akeneo.category.discover_enriched_categories_information.survey_link_label')}
          </Link>
        </Footer>
      </Information>
    </Container>
  );
};

export {DiscoverEnrichedCategoriesInformationHelper};
