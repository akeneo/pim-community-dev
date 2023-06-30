import React, {FC} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {getColor, Information, Link, ProductCategoryIllustration} from 'akeneo-design-system';
import styled from 'styled-components';

const Container = styled.div`
  margin-bottom: 20px;
`;

const Footer = styled.div`
  display: flex;
  gap: 1em;
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

const surveyLink = 'https://www.surveymonkey.com/r/3K7JWK3';

const DiscoverEnrichedCategoriesInformationHelper: FC = () => {
  const translate = useTranslate();

  return (
    <Container data-testid="discover-enriched-categories-information-helper">
      <Information
        illustration={<ProductCategoryIllustration />}
        title={translate('akeneo.category.discover_enriched_categories_information.title')}
      >
        <List>
          <Item>
            {translate('akeneo.category.discover_enriched_categories_information.content.enrich_attribute_values')}
          </Item>
          <Item>
            {translate('akeneo.category.discover_enriched_categories_information.content.customizable_templates')}
          </Item>
        </List>
        <Footer>
          <Link href="https://help.akeneo.com/pim/serenity/articles/enrich-your-category.html" target="_blank">
            {translate('akeneo.category.discover_enriched_categories_information.learn_more_link_label')}
          </Link>
          <Link href={surveyLink} target="_blank">
            {translate('akeneo.category.discover_enriched_categories_information.survey_link_label')}
          </Link>
        </Footer>
      </Information>
    </Container>
  );
};

export {DiscoverEnrichedCategoriesInformationHelper};
