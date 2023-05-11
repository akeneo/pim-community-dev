import React, {FC, useCallback} from 'react';
import {Breadcrumb, Button, Checkbox, SectionTitle} from 'akeneo-design-system';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {useConfiguration} from './useConfiguration';

const Section = styled.section`
  display: flex;
  flex-direction: column;
  margin-top: 20px;
`;
const Content = styled.div``;

const InlineButton = styled(Button);

const InlineContainer = styled.div`
  display: flex;
  margin: 10px;
  gap: 10px;
  ${InlineButton} {
    margin: 10px;
  }
`;

const Field = styled.div`
  display: flex;
  margin: 10px 0;
`;

const Page: FC = () => {
  const {configuration, setDefaultCommunitySettings, setDefaultEnterpriseSettings, updateConfiguration} =
    useConfiguration();

  const changeFeature = useCallback(
    (feature: string, value: boolean) => {
      updateConfiguration({
        features: {
          [feature]: value,
        },
      });
    },
    [updateConfiguration]
  );

  const changeAcl = useCallback(
    (property: string, value: boolean) => {
      updateConfiguration({
        acls: {
          [property]: value,
        },
      });
    },
    [updateConfiguration]
  );

  return (
    <>
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
            <SectionTitle.Title>Editions</SectionTitle.Title>
          </SectionTitle>

          <Content>
            <InlineContainer>
              <Button onClick={setDefaultCommunitySettings}>Community</Button>
              <Button onClick={setDefaultEnterpriseSettings}>Enterprise</Button>
            </InlineContainer>
          </Content>
        </Section>

        <Section>
          <SectionTitle>
            <SectionTitle.Title>Feature Flags</SectionTitle.Title>
          </SectionTitle>
          <Content>
            <Field>
              <Checkbox
                checked={configuration.features.permission}
                onClick={() => changeFeature('permission', !configuration.features.permission)}
              >
                Permission
              </Checkbox>
            </Field>
            <Field>
              <Checkbox
                checked={configuration.features.enriched_category}
                onClick={() => changeFeature('enriched_category', !configuration.features.enriched_category)}
              >
                Enriched Categories
              </Checkbox>
            </Field>
            <Field>
              <Checkbox
                checked={configuration.features.category_template_customization}
                onClick={() =>
                  changeFeature(
                    'category_template_customization',
                    !configuration.features.category_template_customization
                  )
                }
              >
                Enriched Categories: Customize Template
              </Checkbox>
            </Field>
            <Field>
              <Checkbox
                  checked={configuration.features.category_update_template_attribute}
                  onClick={() =>
                      changeFeature(
                          'category_update_template_attribute',
                          !configuration.features.category_update_template_attribute
                      )
                  }
              >
                Enriched Categories: Update Templace Attribute
              </Checkbox>
            </Field>
          </Content>
        </Section>

        <Section>
          <SectionTitle>
            <SectionTitle.Title>ACLs</SectionTitle.Title>
          </SectionTitle>
          <Content>
            <Field>
              <Checkbox checked={configuration.acls.pim_enrich_product_categories_view}>View a category</Checkbox>
            </Field>
            <Field>
              <Checkbox
                checked={configuration.acls.pim_enrich_product_category_create}
                onClick={() =>
                  changeAcl(
                    'pim_enrich_product_category_create',
                    !configuration.acls.pim_enrich_product_category_create
                  )
                }
              >
                Create a category
              </Checkbox>
            </Field>

            <Field>
              <Checkbox
                checked={configuration.acls.pim_enrich_product_category_edit}
                onClick={() =>
                  changeAcl('pim_enrich_product_category_edit', !configuration.acls.pim_enrich_product_category_edit)
                }
              >
                Edit a category
              </Checkbox>
            </Field>

            <Field>
              <Checkbox
                checked={configuration.acls.pim_enrich_product_category_history}
                onClick={() =>
                  changeAcl(
                    'pim_enrich_product_category_history',
                    !configuration.acls.pim_enrich_product_category_history
                  )
                }
              >
                View category history
              </Checkbox>
            </Field>

            <Field>
              <Checkbox
                checked={configuration.acls.pim_enrich_product_category_list}
                onClick={() =>
                  changeAcl('pim_enrich_product_category_list', !configuration.acls.pim_enrich_product_category_list)
                }
              >
                List categories
              </Checkbox>
            </Field>

            <Field>
              <Checkbox
                checked={configuration.acls.pim_enrich_product_category_order_trees}
                onClick={() =>
                  changeAcl(
                    'pim_enrich_product_category_order_trees',
                    !configuration.acls.pim_enrich_product_category_order_trees
                  )
                }
              >
                Order/reorder trees in a category
              </Checkbox>
            </Field>

            <Field>
              <Checkbox
                checked={configuration.acls.pim_enrich_product_category_remove}
                onClick={() =>
                  changeAcl(
                    'pim_enrich_product_category_remove',
                    !configuration.acls.pim_enrich_product_category_remove
                  )
                }
              >
                Remove a category
              </Checkbox>
            </Field>

            <Field>
              <Checkbox
                checked={configuration.acls.pim_enrich_product_category_template}
                onClick={() =>
                  changeAcl(
                    'pim_enrich_product_category_template',
                    !configuration.acls.pim_enrich_product_category_template
                  )
                }
              >
                Manage category template
              </Checkbox>
            </Field>

            <Field>
              <Checkbox
                checked={configuration.acls.pim_enrich_product_category_edit_attributes}
                onClick={() =>
                  changeAcl(
                    'pim_enrich_product_category_edit_attributes',
                    !configuration.acls.pim_enrich_product_category_edit_attributes
                  )
                }
              >
                Edit category attributes
              </Checkbox>
            </Field>

            <Field>
              <Checkbox
                checked={configuration.acls.pimee_enrich_category_edit_permissions}
                onClick={() =>
                  changeAcl(
                    'pimee_enrich_category_edit_permissions',
                    !configuration.acls.pimee_enrich_category_edit_permissions
                  )
                }
              >
                Manage category permissions
              </Checkbox>
            </Field>
          </Content>
        </Section>
      </PageContent>
    </>
  );
};

export {Page};
