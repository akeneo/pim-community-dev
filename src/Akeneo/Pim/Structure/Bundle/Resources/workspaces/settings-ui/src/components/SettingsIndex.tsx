import React from 'react';
import {
  FullScreenError,
  PageContent,
  PageHeader,
  PimView,
  useRouter,
  useSecurity,
  useTranslate,
  Translate,
  useAnalytics,
  useFeatureFlags,
} from '@akeneo-pim-community/shared';
import {
  AssociateIcon,
  AttributeFileIcon,
  AttributeLinkIcon,
  Badge,
  Breadcrumb,
  CategoryIcon,
  ComponentIcon,
  CreditsIcon,
  GroupsIcon,
  IconCard,
  IconCardGrid,
  IdIcon,
  LocaleIcon,
  LockIcon,
  MetricIcon,
  SectionTitle,
  ShopIcon,
  TagIcon,
  useTheme,
  ValueIcon,
} from 'akeneo-design-system';
import styled from 'styled-components';
import {CountEntities, useCountEntities} from '../hooks/settings';

const SectionContent = styled.div`
  margin-top: 20px;
  margin-bottom: 30px;
`;

const getPluralizedTranslation = (
  translate: Translate,
  translationId: string,
  countEntities: CountEntities,
  propertyPath: string
) => {
  return countEntities.hasOwnProperty(propertyPath)
    ? translate(translationId, {count: countEntities[propertyPath]}, countEntities[propertyPath])
    : '';
};

const SettingsIndex = () => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const router = useRouter();
  const theme = useTheme();
  const analytics = useAnalytics();
  const featureFlags = useFeatureFlags();

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

  const canAccessProductSettings =
    canAccessAttributeGroups ||
    canAccessAttributes ||
    canAccessFamilies ||
    canAccessMeasurements ||
    canAccessAssociationTypes ||
    canAccessGroupTypes ||
    canAccessGroups;

  const canAccessRules =
    featureFlags.isEnabled('product_rules') && isGranted('pimee_catalog_rule_rule_view_permissions');
  const canAccessIdentifierGenerator =
    featureFlags.isEnabled('identifier_generator') &&
    (isGranted('pim_identifier_generator_view') || isGranted('pim_identifier_generator_manage'));

  const canAccessAutomationSettings =
    canAccessRules || canAccessIdentifierGenerator || featureFlags.isEnabled('free_trial');

  const redirectToRoute = (route: string) => {
    router.redirect(router.generate(route));
  };

  if (!canAccessCatalogSettings && !canAccessProductSettings && !canAccessAutomationSettings) {
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
                    id="pim-settings-product-category"
                    icon={<CategoryIcon />}
                    label={
                      <>
                        {translate('pim_enrich.entity.category.plural_label')}
                        <StyledBadge level="secondary">{translate('pim_menu.tag.new')}</StyledBadge>
                      </>
                    }
                    onClick={() => redirectToRoute('pim_enrich_categorytree_index')}
                    content={
                      countEntities.hasOwnProperty('count_category_trees') &&
                      countEntities.hasOwnProperty('count_categories')
                        ? getPluralizedTranslation(
                            translate,
                            'pim_settings.count.category_trees',
                            countEntities,
                            'count_category_trees'
                          ).concat(
                            translate(
                              'pim_settings.count.categories',
                              {count: countEntities['count_categories'] - countEntities['count_category_trees']},
                              countEntities['count_categories'] - countEntities['count_category_trees']
                            )
                          )
                        : ''
                    }
                  />
                )}
                {canAccessChannels && (
                  <IconCard
                    id="pim-settings-channel"
                    icon={<ShopIcon />}
                    label={translate('pim_menu.item.channel')}
                    onClick={() => redirectToRoute('pim_enrich_channel_index')}
                    content={getPluralizedTranslation(
                      translate,
                      'pim_settings.count.channels',
                      countEntities,
                      'count_channels'
                    )}
                  />
                )}
                {canAccessLocales && (
                  <IconCard
                    id="pim-settings-locale"
                    icon={<LocaleIcon />}
                    label={translate('pim_enrich.entity.locale.plural_label')}
                    onClick={() => redirectToRoute('pim_enrich_locale_index')}
                    content={getPluralizedTranslation(
                      translate,
                      'pim_settings.count.locales',
                      countEntities,
                      'count_locales'
                    )}
                  />
                )}
                {canAccessCurrencies && (
                  <IconCard
                    id="pim-settings-currency"
                    icon={<CreditsIcon />}
                    label={translate('pim_menu.item.currency')}
                    onClick={() => redirectToRoute('pim_enrich_currency_index')}
                    content={getPluralizedTranslation(
                      translate,
                      'pim_settings.count.currencies',
                      countEntities,
                      'count_currencies'
                    )}
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
                    id="pim-settings-attribute-group"
                    icon={<TagIcon />}
                    label={translate('pim_enrich.entity.attribute_group.plural_label')}
                    onClick={() => redirectToRoute('pim_enrich_attributegroup_index')}
                    content={getPluralizedTranslation(
                      translate,
                      'pim_settings.count.attribute_groups',
                      countEntities,
                      'count_attribute_groups'
                    )}
                  />
                )}
                {canAccessAttributes && (
                  <IconCard
                    id="pim-settings-attribute"
                    icon={<ValueIcon />}
                    label={translate('pim_enrich.entity.attribute.plural_label')}
                    onClick={() => {
                      analytics.appcuesTrack('settings:attributes:clicked');
                      redirectToRoute('pim_enrich_attribute_index');
                    }}
                    content={getPluralizedTranslation(
                      translate,
                      'pim_settings.count.attributes',
                      countEntities,
                      'count_attributes'
                    )}
                  />
                )}
                {canAccessFamilies && (
                  <IconCard
                    id="pim-settings-family"
                    icon={<AttributeFileIcon />}
                    label={translate('pim_menu.item.family')}
                    onClick={() => {
                      analytics.appcuesTrack('settings:families:clicked');
                      redirectToRoute('pim_enrich_family_index');
                    }}
                    content={getPluralizedTranslation(
                      translate,
                      'pim_settings.count.families',
                      countEntities,
                      'count_families'
                    )}
                  />
                )}
                {canAccessMeasurements && (
                  <IconCard
                    id="pim-measurements-settings"
                    icon={<MetricIcon />}
                    label={translate('pim_menu.item.measurements')}
                    onClick={() => redirectToRoute('akeneo_measurements_settings_index')}
                    content={getPluralizedTranslation(
                      translate,
                      'pim_settings.count.measurements',
                      countEntities,
                      'count_measurements'
                    )}
                  />
                )}
                {canAccessAssociationTypes && (
                  <IconCard
                    id="pim-settings-association-type"
                    icon={<AssociateIcon />}
                    label={translate('pim_menu.item.association_type')}
                    onClick={() => redirectToRoute('pim_enrich_associationtype_index')}
                    content={getPluralizedTranslation(
                      translate,
                      'pim_settings.count.association_types',
                      countEntities,
                      'count_association_types'
                    )}
                  />
                )}
                {canAccessGroupTypes && (
                  <IconCard
                    id="pim-settings-group-type"
                    icon={<ComponentIcon />}
                    label={translate('pim_menu.item.group_type')}
                    onClick={() => redirectToRoute('pim_enrich_grouptype_index')}
                    content={getPluralizedTranslation(
                      translate,
                      'pim_settings.count.group_types',
                      countEntities,
                      'count_group_types'
                    )}
                  />
                )}
                {canAccessGroups && (
                  <IconCard
                    id="pim-settings-group"
                    icon={<GroupsIcon />}
                    label={translate('pim_menu.item.group')}
                    onClick={() => redirectToRoute('pim_enrich_group_index')}
                    content={getPluralizedTranslation(
                      translate,
                      'pim_settings.count.groups',
                      countEntities,
                      'count_groups'
                    )}
                  />
                )}
              </IconCardGrid>
            </SectionContent>
          </>
        )}
        {canAccessAutomationSettings && (
          <>
            <SectionTitle>
              <SectionTitle.Title>{translate('pim_settings.automation_settings')}</SectionTitle.Title>
            </SectionTitle>
            <SectionContent>
              <IconCardGrid>
                {canAccessRules && (
                  <IconCard
                    id="pim-enrich-rule"
                    icon={<AttributeLinkIcon />}
                    label={translate('pim_menu.item.rule')}
                    onClick={() => redirectToRoute('pimee_catalog_rule_rule_index')}
                    content={getPluralizedTranslation(
                      translate,
                      'pim_settings.count.rules',
                      countEntities,
                      'count_rules'
                    )}
                  />
                )}
                {canAccessIdentifierGenerator && (
                  <IconCard
                    id="pim-enrich-identifier-generator"
                    icon={<IdIcon />}
                    label={
                      <>
                        {translate('pim_title.akeneo_identifier_generator_index')}
                        <StyledBadge level="secondary">{translate('pim_menu.tag.new')}</StyledBadge>
                      </>
                    }
                    onClick={() => redirectToRoute('akeneo_identifier_generator_index')}
                    content={getPluralizedTranslation(
                      translate,
                      'pim_settings.count.identifier_generators',
                      countEntities,
                      'count_identifier_generators'
                    )}
                  />
                )}
                {featureFlags.isEnabled('free_trial') && (
                  <DisableIconCard
                    id="pim-enrich-rule"
                    icon={
                      <LockIconContainer>
                        <LockIcon size={16} color={theme.color.blue100} />
                      </LockIconContainer>
                    }
                    label={translate('free_trial.menu.rules')}
                    content={translate('free_trial.menu.feature_ee_only')}
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

const DisableIconCard = styled(IconCard)`
  cursor: pointer;
  border: 1px rgba(240, 241, 243, 0.5) solid;

  :hover {
    background: #fff;
    border: 1px rgba(240, 241, 243, 0.5) solid;
  }

  > *:not(:first-child) {
    opacity: 0.5;
  }
`;

const LockIconContainer = styled.div`
  border: 1px solid #4ca8e0;
  border-radius: 4px;
  background: #f0f7fc;
  height: 24px;
  width: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
`;

const StyledBadge = styled(Badge)`
  margin-left: 10px;
`;

export {SettingsIndex};
