import React, {FC} from 'react';
import {Breadcrumb, Button, Checkbox, SectionTitle} from "akeneo-design-system";
import {PageContent, PageHeader} from "@akeneo-pim-community/shared";
import styled from "styled-components";
import {useConfiguration} from "./useConfiguration";

const Section = styled.section`
  display: flex;
  flex-direction: column;
  margin-top: 20px;
`;
const Content = styled.div`
`;

const InlineContainer = styled.div`
  display: flex;
  margin: 10pw;
  gap: 10px;
  ${Button} {
    margin: 10px;
  }
`;

const Field = styled.div`
  display: flex;
  margin: 10px 0;
`;

const Page: FC = () => {
  const {configuration, setDefaultCommunitySettings, setDefaultEnterpriseSettings, setDefaultGrowthSettings} = useConfiguration();
  return (<>
    <PageHeader>
      <PageHeader.Breadcrumb>
        <Breadcrumb>
          <Breadcrumb.Step href={`#/`}>App</Breadcrumb.Step>
          <Breadcrumb.Step>Configuration</Breadcrumb.Step>
        </Breadcrumb>
      </PageHeader.Breadcrumb>
      <PageHeader.Title>Configuration</PageHeader.Title>
    </PageHeader>
    <PageContent>
      <Section>
        <SectionTitle>
          <SectionTitle.Title>
            Editions
          </SectionTitle.Title>
        </SectionTitle>

        <Content>
          <InlineContainer>
            <Button onClick={setDefaultCommunitySettings}>Community</Button>
            <Button onClick={setDefaultGrowthSettings}>Growth</Button>
            <Button onClick={setDefaultEnterpriseSettings}>Enterprise</Button>
          </InlineContainer>
        </Content>
      </Section>

      <Section>
        <SectionTitle>
          <SectionTitle.Title>
            Feature Flags
          </SectionTitle.Title>
        </SectionTitle>
        <Content>
          <Field>
            <Checkbox checked={configuration.features.permission}>
              Permission
            </Checkbox>
          </Field>
          <Field>
            <Checkbox checked={configuration.features.enrich_category}>
              Enrich Category
            </Checkbox>
          </Field>
        </Content>
      </Section>

      <Section>
        <SectionTitle>
          <SectionTitle.Title>
            ACLs
          </SectionTitle.Title>
        </SectionTitle>
        <Content>
          {/*
          <Field>
            <Checkbox checked={configuration.acls.pim_enrich_product_categories_view}>
              View a category
            </Checkbox>
          </Field>
*/}
          <Field>
            <Checkbox checked={configuration.acls.pim_enrich_product_category_create}>
              Create a category
            </Checkbox>
          </Field>

          <Field>
            <Checkbox checked={configuration.acls.pim_enrich_product_category_edit}>
              Edit a category
            </Checkbox>
          </Field>

          <Field>
            <Checkbox checked={configuration.acls.pim_enrich_product_category_history}>
              View category history
            </Checkbox>
          </Field>

          <Field>
            <Checkbox checked={configuration.acls.pim_enrich_product_category_list}>
              List categories
            </Checkbox>
          </Field>

          <Field>
            <Checkbox checked={configuration.acls.pim_enrich_product_category_remove}>
              Remove a category
            </Checkbox>
          </Field>

          <Field>
            <Checkbox checked={configuration.acls.pimee_enrich_category_edit_permissions}>
              Manage category permissions
            </Checkbox>
          </Field>
        </Content>
      </Section>
    </PageContent>
  </>);
};

export {Page}
