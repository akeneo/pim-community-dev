import {Button, SectionTitle} from "akeneo-design-system";
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
            <SectionTitle sticky={0}>
                <SectionTitle.Title>
                    {attribute.labels[catalogLocale]}{' '}{translate('akeneo.category.template.attribute.settings.title')}
                </SectionTitle.Title>
            </SectionTitle><Footer sticky={0}>
                <DeleteButton level="danger" ghost>{translate('akeneo.category.template.attribute.delete_button')}</DeleteButton>
            </Footer>
        </SettingsContainer>
    );
}

const SettingsContainer = styled.div`
  display: flex;
  flex-direction: column;
  padding-left: 40px;
  width: 510px;
  overflow-y: auto;
`;

const DeleteButton = styled(Button)`
  float: right;
`;

const Footer = styled(SectionTitle)`
  border-bottom: 0;
  padding: 10px 0 20px;
  margin-top: 2px;
  justify-content: flex-end;
  position: sticky;
  bottom: 0;
  background-color: #ffffff;
  z-index: 10;
`;
