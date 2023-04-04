import {Button, SectionTitle } from "akeneo-design-system";
import {Attribute} from "../../models";
import React from "react";
import {userContext, useTranslate} from "@akeneo-pim-community/shared";
import styled from "styled-components";

type Props = {
    attribute: Attribute;
};

export const AttributeSettings = ({attribute}: Props) => {
    const translate = useTranslate();
    const catalogLocale = userContext.get('catalogLocale');
    return (
        <SettingsContainer>
            <Body>
                <SectionTitle sticky={0}>
                    <SectionTitle.Title>
                        {attribute.labels[catalogLocale]}{' '}{translate('akeneo.category.template.attribute.settings.title')}
                    </SectionTitle.Title>
                </SectionTitle>
            </Body>
            <Footer>
                <DeleteButton level="danger" ghost>{translate('akeneo.category.template.attribute.delete_button')}</DeleteButton>
            </Footer>
        </SettingsContainer>
    );
}

const SettingsContainer = styled.div`
  display: flex;
  flex-direction: column;
`;

const Body = styled.div`
  display: flex;
  flex-direction: column;
  width: 550px;
  padding-left: 40px;
  margin-top: 2px;
  overflow-y: auto; //TODO jouer avec ce CSS
  max-height: 55vh;
`;

const DeleteButton = styled(Button)`
  float: right;
`;

const Footer = styled(SectionTitle)`
  border-bottom: 0;
  justify-content: flex-end;
  position: sticky;
  top: 100px;
  background-color: #ffffff;
  z-index: 9;
`;
