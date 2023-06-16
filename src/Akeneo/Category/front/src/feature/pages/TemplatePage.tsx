import {
  getLabel,
  PageHeader,
  PimView,
  useFeatureFlags,
  useRouter,
  useSessionStorageState,
  useTranslate,
  useUserContext,
} from '@akeneo-pim-community/shared';
import {Breadcrumb, Pill, SkeletonPlaceholder, TabBar, useBooleanState, useTabBar} from 'akeneo-design-system';
import {FC} from 'react';
import {useParams} from 'react-router';
import styled from 'styled-components';
import {useTemplateForm} from '../components/providers/TemplateFormProvider';
import {DeactivateTemplateModal} from '../components/templates/DeactivateTemplateModal';
import {EditTemplateAttributesForm} from '../components/templates/EditTemplateAttributesForm';
import {EditTemplatePropertiesForm} from '../components/templates/EditTemplatePropertiesForm';
import {SaveStatusIndicator} from '../components/templates/SaveStatusIndicator';
import {TemplateOtherActions} from '../components/templates/TemplateOtherActions';
import {useCategoryTree, useTemplateByTemplateUuid} from '../hooks';

enum Tabs {
  ATTRIBUTE = '#pim_enrich-category-tab-attribute',
  PROPERTY = '#pim_enrich-category-tab-property',
}

const useTabInError = () => {
  const [state] = useTemplateForm();

  return {
    [Tabs.ATTRIBUTE]: Object.values(state.attributes).some(translations =>
      Object.values(translations || {}).some(({errors}) => errors.length > 0)
    ),
    [Tabs.PROPERTY]: false,
  };
};

type Params = {
  treeId: string;
  templateId: string;
};

const TemplatePage: FC = () => {
  const router = useRouter();
  const translate = useTranslate();
  const userContext = useUserContext();
  const featureFlag = useFeatureFlags();

  const [activeTab, setActiveTab] = useSessionStorageState<string>(Tabs.ATTRIBUTE, 'pim_category_template_activeTab');
  const [isCurrent, switchTo] = useTabBar(activeTab);

  const handleSwitchTo = (tab: string) => {
    setActiveTab(tab);
    switchTo(tab);
  };

  const tabInError = useTabInError();

  const {treeId, templateId} = useParams<Params>();
  const {data: tree} = useCategoryTree(treeId);
  const {data: template} = useTemplateByTemplateUuid(templateId);

  const catalogLocale = userContext.get('catalogLocale');
  const templateLabel = template && getLabel(template.labels, catalogLocale, template.code);

  const [isDeactivateTemplateModelOpen, openDeactivateTemplateModal, closeDeactivateTemplateModal] = useBooleanState();

  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step onClick={() => router.redirect(router.generate('pim_settings_index'))}>
              {translate('pim_menu.tab.settings')}
            </Breadcrumb.Step>
            <Breadcrumb.Step onClick={() => router.redirect(router.generate('pim_enrich_categorytree_index'))}>
              {translate('pim_enrich.entity.category.plural_label')}
            </Breadcrumb.Step>
            <Breadcrumb.Step
              onClick={() => router.redirect(router.generate('pim_enrich_categorytree_tree', {id: treeId}))}
            >
              {tree?.label || <SkeletonPlaceholder as="span">{treeId}</SkeletonPlaceholder>}
            </Breadcrumb.Step>
            <Breadcrumb.Step>
              {templateLabel || <SkeletonPlaceholder as="span">{templateId}</SkeletonPlaceholder>}
            </Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        {featureFlag.isEnabled('category_update_template_attribute') && (
          <PageHeader.AutoSaveStatus>
            <SaveStatusIndicator />
          </PageHeader.AutoSaveStatus>
        )}
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Actions>
          <TemplateOtherActions onDeactivateTemplate={openDeactivateTemplateModal} />
        </PageHeader.Actions>
        <PageHeader.Title>{templateLabel}</PageHeader.Title>
      </PageHeader>
      <PageContent>
        <TabBar moreButtonTitle={'More'} sticky={0}>
          <TabBar.Tab isActive={isCurrent(Tabs.ATTRIBUTE)} onClick={() => handleSwitchTo(Tabs.ATTRIBUTE)}>
            {translate('akeneo.category.attributes')} {tabInError[Tabs.ATTRIBUTE] && <Pill level={'danger'} />}
          </TabBar.Tab>
          <TabBar.Tab isActive={isCurrent(Tabs.PROPERTY)} onClick={() => handleSwitchTo(Tabs.PROPERTY)}>
            {translate('pim_common.properties')} {tabInError[Tabs.PROPERTY] && <Pill level={'danger'} />}
          </TabBar.Tab>
        </TabBar>

        {template && (
          <>
            {isCurrent(Tabs.ATTRIBUTE) && (
              <EditTemplateAttributesForm attributes={template.attributes} templateId={template.uuid} />
            )}

            {isCurrent(Tabs.PROPERTY) && <EditTemplatePropertiesForm template={template} />}

            {isDeactivateTemplateModelOpen && (
              <DeactivateTemplateModal
                template={{id: template.uuid, label: templateLabel || ''}}
                onClose={closeDeactivateTemplateModal}
              />
            )}
          </>
        )}
      </PageContent>
    </>
  );
};

const PageContent = styled.div`
  padding: 0 40px;
  height: calc(100vh - 130px);
`;

export {TemplatePage};
