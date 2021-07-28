import React from 'react';
import {
  FullScreenError,
  PageContent,
  PageHeader,
  useRouter,
  useSecurity,
  useTranslate,
  PimView,
} from '@akeneo-pim-community/shared';
import {
  AssociateIcon,
  AttributeFileIcon,
  AttributeLinkIcon,
  Breadcrumb,
  CategoryIcon,
  ComponentIcon,
  CreditsIcon,
  GroupsIcon,
  IconCard,
  IconCardGrid,
  LocaleIcon,
  MetricIcon,
  SectionTitle,
  ShopIcon,
  TagIcon,
  ValueIcon,
} from 'akeneo-design-system';
import styled from 'styled-components';
import {useCountEntities} from '../hooks/settings';

const SectionContent = styled.div`
  margin-top: 20px;
  margin-bottom: 30px;
`;

const SettingsIndex = () => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const router = useRouter();

  const canAccessCategories = isGranted('pim_enrich_product_category_list');
  const canAccessChannels = isGranted('pim_enrich_channel_index');
  const canAccessLocales = isGranted('pim_enrich_locale_index');
  const canAccessCurrencies = isGranted('pim_enrich_currency_index');

  const canAccessCatalogSettings = canAccessCategories || canAccessChannels || canAccessLocales || canAccessCurrencies;

  const canAccessAttributeGroups = isGranted('pim_enrich_attributegroup_index');
  const canAccessAttributes = isGranted('pim_enrich_attribute_index');
  const canAccessFamilies = isGranted('pim_enrich_family_index');
  const canAccessMeasurements = isGranted('akeneo_measurements_manage_settings');
  const canAccessAssociationTypes = isGranted('pim_enrich_associationtype_index');
  const canAccessGroupTypes = isGranted('pim_enrich_grouptype_index');
  const canAccessGroups = isGranted('pim_enrich_group_index');
  const canAccessRules = isGranted('pimee_catalog_rule_rule_view_permissions');

  const canAccessProductSettings =
    canAccessAttributeGroups ||
    canAccessAttributes ||
    canAccessFamilies ||
    canAccessMeasurements ||
    canAccessAssociationTypes ||
    canAccessGroupTypes ||
    canAccessGroups ||
    canAccessRules;

  const redirectToRoute = (route: string) => {
    router.redirect(router.generate(route));
  };

  if (!canAccessCatalogSettings && !canAccessProductSettings) {
    return (
      <FullScreenError
        title={translate('error.exception', {status_code: 403})}
        message={translate('error.forbidden')}
        code={403}
      />
    );
  }

  const countEntities = useCountEntities();

  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step>{translate('pim_menu.tab.settings')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Title>{translate('pim_settings.settings_menu')}</PageHeader.Title>
      </PageHeader>
      <PageContent>
        {canAccessCatalogSettings && (
          <>
            <SectionTitle>
              <SectionTitle.Title>{translate('pim_settings.catalog_settings')}</SectionTitle.Title>
            </SectionTitle>
            <SectionContent>
              <IconCardGrid>
                {canAccessCategories && (
                  <IconCard
                    icon={<CategoryIcon />}
                    label={translate('pim_enrich.entity.category.plural_label')}
                    onClick={() => redirectToRoute('pim_enrich_categorytree_index')}
                    content={
                      countEntities.hasOwnProperty('count_category_trees') &&
                      countEntities.hasOwnProperty('count_categories')
                        ? translate('pim_settings.count.categories', {
                            countTrees: countEntities['count_category_trees'],
                            countCategories: countEntities['count_categories'] - countEntities['count_category_trees'],
                          })
                        : ''
                    }
                  />
                )}
                {canAccessChannels && (
                  <IconCard
                    icon={<ShopIcon />}
                    label={translate('pim_menu.item.channel')}
                    onClick={() => redirectToRoute('pim_enrich_channel_index')}
                    content={
                      countEntities.hasOwnProperty('count_channels')
                        ? countEntities['count_channels'] === 1
                          ? translate('pim_settings.count.channel')
                          : translate('pim_settings.count.channels', {count: countEntities['count_channels']})
                        : ''
                    }
                  />
                )}
                {canAccessLocales && (
                  <IconCard
                    icon={<LocaleIcon />}
                    label={translate('pim_enrich.entity.locale.plural_label')}
                    onClick={() => redirectToRoute('pim_enrich_locale_index')}
                    content={
                      countEntities.hasOwnProperty('count_locales')
                        ? translate('pim_settings.count.locales', {count: countEntities['count_locales']})
                        : ''
                    }
                  />
                )}
                {canAccessCurrencies && (
                  <IconCard
                    icon={<CreditsIcon />}
                    label={translate('pim_menu.item.currency')}
                    onClick={() => redirectToRoute('pim_enrich_currency_index')}
                    content={
                      countEntities.hasOwnProperty('count_currencies')
                        ? translate('pim_settings.count.currencies', {count: countEntities['count_currencies']})
                        : ''
                    }
                  />
                )}
              </IconCardGrid>
            </SectionContent>
          </>
        )}
        {canAccessProductSettings && (
          <>
            <SectionTitle>
              <SectionTitle.Title>{translate('pim_settings.product_settings')}</SectionTitle.Title>
            </SectionTitle>
            <SectionContent>
              <IconCardGrid>
                {canAccessAttributeGroups && (
                  <IconCard
                    icon={<TagIcon />}
                    label={translate('pim_enrich.entity.attribute_group.plural_label')}
                    onClick={() => redirectToRoute('pim_enrich_attributegroup_index')}
                    content={
                      countEntities.hasOwnProperty('count_attribute_groups')
                        ? translate('pim_settings.count.attribute_groups', {
                            count: countEntities['count_attribute_groups'],
                          })
                        : ''
                    }
                  />
                )}
                {canAccessAttributes && (
                  <IconCard
                    icon={<ValueIcon />}
                    label={translate('pim_enrich.entity.attribute.plural_label')}
                    onClick={() => redirectToRoute('pim_enrich_attribute_index')}
                    content={
                      countEntities.hasOwnProperty('count_attributes')
                        ? translate('pim_settings.count.attributes', {count: countEntities['count_attributes']})
                        : ''
                    }
                  />
                )}
                {canAccessFamilies && (
                  <IconCard
                    icon={<AttributeFileIcon />}
                    label={translate('pim_menu.item.family')}
                    onClick={() => redirectToRoute('pim_enrich_family_index')}
                    content={
                      countEntities.hasOwnProperty('count_families')
                        ? translate('pim_settings.count.families', {count: countEntities['count_families']})
                        : ''
                    }
                  />
                )}
                {canAccessMeasurements && (
                  <IconCard
                    icon={<MetricIcon />}
                    label={translate('pim_menu.item.measurements')}
                    onClick={() => redirectToRoute('akeneo_measurements_settings_index')}
                    content={
                      countEntities.hasOwnProperty('count_measurements')
                        ? translate('pim_settings.count.measurements', {count: countEntities['count_measurements']})
                        : ''
                    }
                  />
                )}
                {canAccessAssociationTypes && (
                  <IconCard
                    icon={<AssociateIcon />}
                    label={translate('pim_menu.item.association_type')}
                    onClick={() => redirectToRoute('pim_enrich_associationtype_index')}
                    content={
                      countEntities.hasOwnProperty('count_association_types')
                        ? translate('pim_settings.count.association_types', {
                            count: countEntities['count_association_types'],
                          })
                        : ''
                    }
                  />
                )}
                {canAccessGroupTypes && (
                  <IconCard
                    icon={<ComponentIcon />}
                    label={translate('pim_menu.item.group_type')}
                    onClick={() => redirectToRoute('pim_enrich_grouptype_index')}
                    content={
                      countEntities.hasOwnProperty('count_group_types')
                        ? translate('pim_settings.count.group_types', {count: countEntities['count_group_types']})
                        : ''
                    }
                  />
                )}
                {canAccessGroups && (
                  <IconCard
                    icon={<GroupsIcon />}
                    label={translate('pim_menu.item.group')}
                    onClick={() => redirectToRoute('pim_enrich_group_index')}
                    content={
                      countEntities.hasOwnProperty('count_groups')
                        ? translate('pim_settings.count.groups', {count: countEntities['count_groups']})
                        : ''
                    }
                  />
                )}
                {canAccessRules && (
                  <IconCard
                    icon={<AttributeLinkIcon />}
                    label={translate('pim_menu.item.rule')}
                    onClick={() => redirectToRoute('pimee_catalog_rule_rule_index')}
                    content={
                      countEntities.hasOwnProperty('count_rules')
                        ? translate('pim_settings.count.rules', {count: countEntities['count_rules']})
                        : ''
                    }
                  />
                )}
              </IconCardGrid>
            </SectionContent>
          </>
        )}
      </PageContent>
    </>
  );
};

export {SettingsIndex};
