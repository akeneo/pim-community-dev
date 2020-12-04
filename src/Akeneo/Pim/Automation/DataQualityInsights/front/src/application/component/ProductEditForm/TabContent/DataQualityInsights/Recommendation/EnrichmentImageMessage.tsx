import React, {FC} from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Link} from 'akeneo-design-system';

const Container = styled.span`
  font-style: italic;
`;

const LinkableMessage = styled(Link)`
  text-decoration: none;
`;

const NotApplicableEnrichmentImageMessage: FC = () => {
  const translate = useTranslate();

  return (
    <Container>
      <LinkableMessage>
        {translate('akeneo_data_quality_insights.product_evaluation.messages.add_image_attribute_recommendation')}
      </LinkableMessage>
    </Container>
  );
};

const ToImproveEnrichmentImageMessage: FC = () => {
  const translate = useTranslate();

  return (
    <Container>
      {translate('akeneo_data_quality_insights.product_evaluation.messages.fill_image_attribute_recommendation')}
    </Container>
  );
};

export {NotApplicableEnrichmentImageMessage, ToImproveEnrichmentImageMessage};
