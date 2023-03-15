import {
  getLabel,
  PageContent,
  PageHeader,
  PimView,
  useFeatureFlags,
  useRouter,
  useSessionStorageState,
  useTranslate,
  useUserContext,
} from '@akeneo-pim-community/shared';
import {Breadcrumb, SkeletonPlaceholder, TabBar, useBooleanState, useTabBar} from 'akeneo-design-system';
import {DeactivateTemplateModal} from '../components/templates/DeactivateTemplateModal';
import {cloneDeep, set} from 'lodash/fp';
import {FC, useCallback, useEffect, useState} from 'react';
import {useParams} from 'react-router';
import {EditTemplateAttributesForm} from '../components/templates/EditTemplateAttributesForm';
import {EditTemplatePropertiesForm} from '../components/templates/EditTemplatePropertiesForm';
import {TemplateOtherActions} from '../components/templates/TemplateOtherActions';
import {useCategoryTree, useTemplateByTemplateUuid} from '../hooks';
import {Template} from '../models';
import {AddTemplateAttributeModal} from "../components/templates/AddTemplateAttributeModal";
import {Button} from "akeneo-design-system/lib/components/Button/Button";
import styled from "styled-components";

enum Tabs {
  ATTRIBUTE = '#pim_enrich-category-tab-attribute',
  PROPERTY = '#pim_enrich-category-tab-property',
}

type Params = {
  treeId: string;
  templateId: string;
};

const AddAttributeButton = styled(Button)`
  margin-left: auto;
`;

const TemplatePage: FC = () => {
  const {treeId, templateId} = useParams<Params>();
  const router = useRouter();
  const translate = useTranslate();
  const userContext = useUserContext();
  const featureFlags = useFeatureFlags();

  const catalogLocale = userContext.get('catalogLocale');

  const {tree, loadingStatus, loadTree} = useCategoryTree(parseInt(treeId), '-1');
  const [templateLabel, setTemplateLabel] = useState('');

  const [treeLabel, setTreeLabel] = useState<string>('');
  const [templateEdited, setTemplateEdited] = useState<Template | null>(null);
  const followSettingsIndex = useCallback(() => router.redirect(router.generate('pim_settings_index')), [router]);
  const followCategoriesIndex = useCallback(
    () => router.redirect(router.generate('pim_enrich_categorytree_index')),
    [router]
  );
  const followCategoryTree = useCallback(() => {
    if (!tree) {
      return;
    }
    router.redirect(router.generate('pim_enrich_categorytree_tree', {id: tree.id}));
  }, [router, tree]);

  const [activeTab, setActiveTab] = useSessionStorageState<string>(Tabs.ATTRIBUTE, 'pim_category_template_activeTab');
  const [isCurrent, switchTo] = useTabBar(activeTab);

  const handleSwitchTo = useCallback(
    (tab: string) => {
      setActiveTab(tab);
      switchTo(tab);
    },
    [setActiveTab, switchTo]
  );

  const {data: fetchedTemplate, status: templateFetchingStatus} = useTemplateByTemplateUuid(templateId);

  useEffect(() => {
    loadTree();
  }, [loadTree]);

  useEffect(() => {
    setTreeLabel(tree ? tree.label : '');
  }, [tree]);

  useEffect(() => {
    if (templateFetchingStatus === 'fetched' && fetchedTemplate) {
      setTemplateEdited(cloneDeep(fetchedTemplate));
    }
  }, [catalogLocale, fetchedTemplate, templateFetchingStatus]);

  useEffect(() => {
    templateEdited && setTemplateLabel(getLabel(templateEdited.labels, catalogLocale, templateEdited.code));
  }, [catalogLocale, templateEdited]);

  const onChangeTemplateLabel = useCallback(
    (localeCode: string, label: string) => {
      if (templateEdited === null) {
        return;
      }

      templateEdited && setTemplateEdited(set(['labels', localeCode], label, templateEdited));
    },
    [templateEdited]
  );

  const [isDeactivateTemplateModelOpen, openDeactivateTemplateModal, closeDeactivateTemplateModal] = useBooleanState();
  const [isAddTemplateAttributeModalOpen, openAddTemplateAttributeModal, closeAddTemplateAttributeModal] = useBooleanState(false);

  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step onClick={followSettingsIndex}>{translate('pim_menu.tab.settings')}</Breadcrumb.Step>
            <Breadcrumb.Step onClick={followCategoriesIndex}>
              {translate('pim_enrich.entity.category.plural_label')}
            </Breadcrumb.Step>
            <Breadcrumb.Step onClick={followCategoryTree}>
              {treeLabel || <SkeletonPlaceholder as="span">{treeId}</SkeletonPlaceholder>}
            </Breadcrumb.Step>
            <Breadcrumb.Step>
              {templateLabel || <SkeletonPlaceholder as="span">{templateId}</SkeletonPlaceholder>}
            </Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        {featureFlags.isEnabled('category_template_deactivation') && (
          <PageHeader.Actions>
            <TemplateOtherActions onDeactivateTemplate={openDeactivateTemplateModal} />
          </PageHeader.Actions>
        )}
        <PageHeader.Title>{templateLabel ?? templateId}</PageHeader.Title>
      </PageHeader>
      <PageContent>
        <TabBar moreButtonTitle={'More'} sticky={0}>
          <TabBar.Tab
            isActive={isCurrent(Tabs.ATTRIBUTE)}
            onClick={() => {
              handleSwitchTo(Tabs.ATTRIBUTE);
            }}
          >
            {translate('akeneo.category.attributes')}
          </TabBar.Tab>
          <TabBar.Tab
            isActive={isCurrent(Tabs.PROPERTY)}
            onClick={() => {
              handleSwitchTo(Tabs.PROPERTY);
            }}
          >
            {translate('pim_common.properties')}
          </TabBar.Tab>
          <AddAttributeButton
              active
              ghost
              disabled={!!templateEdited && templateEdited.attributes.length >= 50}
              level="tertiary"
              onClick={openAddTemplateAttributeModal}
          >
            {translate('akeneo.category.template.add_attribute.add_button')}
          </AddAttributeButton>
        </TabBar>

        {isCurrent(Tabs.ATTRIBUTE) && tree && templateEdited && (
          <EditTemplateAttributesForm attributes={templateEdited.attributes} />
        )}

        {isCurrent(Tabs.PROPERTY) && tree && templateEdited && (
          <EditTemplatePropertiesForm template={templateEdited} onChangeLabel={onChangeTemplateLabel} />
        )}

        {isDeactivateTemplateModelOpen && (
          <DeactivateTemplateModal
            template={{id: templateId, label: templateLabel}}
            onClose={closeDeactivateTemplateModal}
          />
        )}
        {
          featureFlags.isEnabled('category_template_customization')
          && isAddTemplateAttributeModalOpen && (
          <AddTemplateAttributeModal
              template_id={templateId}
              onClose={closeAddTemplateAttributeModal}
          />
        )}
      </PageContent>
    </>
  );
};

export {TemplatePage};
