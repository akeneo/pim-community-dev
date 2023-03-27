import React from 'react';
import {AttributesIllustration, Button, getColor, getFontSize, Link} from 'akeneo-design-system';
import {LabelContainer} from "akeneo-design-system/lib/storybook";
import {translate} from "@akeneo-pim-community/shared";
import styled from "styled-components";

export const NoTemplateAttribute = () =>
{
    return (
        <NoAttributeSection>
            <AttributesIllustration size={256} />
            <Title>
                {translate('akeneo.category.template.add_attribute.no_attribute_title')}
            </Title>
            <Instructions>
                {translate('akeneo.category.template.add_attribute.no_attribute_instructions')}
            </Instructions>
            <Footer>
                <Link href="https://help.akeneo.com/serenity-take-the-power-over-your-products/serenity-enrich-your-category" target="_blank">
                    {translate('akeneo.category.template.learn_more')}
                </Link>
            </Footer>
            <Button
                level="secondary"
                onClick={function noRefCheck(){}}
            >
                {translate('akeneo.category.template.add_attribute.create_button')}
            </Button>
        </NoAttributeSection>
    );
}

const NoAttributeSection = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  margin-top: 70px;
`;

const Title = styled.div`
  font-size: ${getFontSize('title')};
  color: #111111;
  padding: 8px;
  margin: 0 auto;
  max-width: 100%;
`;

const Instructions = styled.div`
  font-size: ${getFontSize('bigger')};
  padding: 8px 0px;
  margin: 0 auto;
  max-width: 550px;
`;

const Footer = styled.div`
  font-size: ${getFontSize('big')};
  display: flex;
  justify-content: center;
  align-items: center;
  text-align: center;
  gap: 1em;
  margin-top: 16px;
  margin-bottom: 32px;
`;
