import React, {FC} from 'react';
import styled, {css} from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Link} from 'akeneo-design-system';

type Props = {
  isVariant: boolean;
};

const Container = styled.span<Props>`  
${({isVariant}) => isVariant && css`
  margin-left: 20px;
`}
`;
const LinkableMessage = styled(Link)`
  text-decoration: none;
`


const NotApplicableEnrichmentImageMessage: FC<Props> = (props) => {
    const translate = useTranslate();

    return (
        <Container {...props}>
            <LinkableMessage>{translate('akeneo_data_quality_insights.product_evaluation.messages.add_image_attribute_recommendation')}</LinkableMessage>
        </Container>
    );
};


const ToImproveEnrichmentImageMessage: FC<Props> = (props) => {
    const translate = useTranslate();

    return (
        <Container {...props}>
            {translate('akeneo_data_quality_insights.product_evaluation.messages.fill_image_attribute_recommendation')}
        </Container>
    );
};

export {NotApplicableEnrichmentImageMessage, ToImproveEnrichmentImageMessage};
