import React, {FC, useEffect, useState} from 'react';
import {Breadcrumb, SkeletonPlaceholder, TabBar, useBooleanState, useTabBar} from 'akeneo-design-system';
import {
  FullScreenError,
  PageContent,
  PageHeader,
  PimView,
  useRouter,
  useSecurity,
  useSessionStorageState,
  useTranslate,
} from '@akeneo-pim-community/shared';
import {useCategoryTree} from '../hooks';
import {useParams} from 'react-router';

const attributeTabName = '#pim_enrich-template-tab-attribute';
const propertyTabName = '#pim_enrich-template-tab-property';

type Params = {
  treeId: string;
  templateId: string;
};

const TemplateIndex: FC = () => {
  const {treeId, templateId} = useParams<Params>();
  const router = useRouter();
  const translate = useTranslate();
  const [isModalOpen, openModal, closeModal] = useBooleanState();
  const {isGranted} = useSecurity();

  const {tree, loadingStatus, loadTree} = useCategoryTree(parseInt(treeId), '-1');
  const templateLabel = 'Template Label';

  const [treeLabel, setTreeLabel] = useState<string>('');
  const followSettingsIndex = () => router.redirect(router.generate('pim_settings_index'));
  const followCategoriesIndex = () => router.redirect(router.generate('pim_enrich_categorytree_index'));
  const followCategoryTree = () => {
    if (!tree) {
      return;
    }
    router.redirect(router.generate('pim_enrich_categorytree_tree', {id: tree.id}));
  };

  const [activeTab, setActiveTab] = useSessionStorageState(attributeTabName, 'pim_category_activeTab');
  const [isCurrent, switchTo] = useTabBar(activeTab);

  useEffect(() => {
    loadTree();
  }, [loadTree, treeId]);

  useEffect(() => {
    setTreeLabel(tree ? tree.label : '');
  }, [tree]);

  if (loadingStatus === 'error') {
    return (
      <FullScreenError
        title={translate('error.exception', {status_code: '404'})}
        message={translate('pim_enrich.entity.category.content.tree.not_found')}
        code={404}
      />
    );
  }

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
        <PageHeader.Title>templateLabel</PageHeader.Title>
      </PageHeader>
      <PageContent>
        <TabBar moreButtonTitle={'More'}>
          <TabBar.Tab
            isActive={isCurrent(attributeTabName)}
            onClick={() => {
              setActiveTab(attributeTabName);
              switchTo(attributeTabName);
            }}
          >
            {translate('akeneo.category.attributes')}
          </TabBar.Tab>
          <TabBar.Tab
            isActive={isCurrent(propertyTabName)}
            onClick={() => {
              setActiveTab(propertyTabName);
              switchTo(propertyTabName);
            }}
          >
            {translate('pim_common.properties')}
          </TabBar.Tab>
        </TabBar>

        {isCurrent(attributeTabName) && tree && <h3>Create Attribute form</h3>}

        {isCurrent(propertyTabName) && tree && <h3>Create Property form</h3>}
      </PageContent>
      {/*{isModalOpen && <NewCategoryModal closeModal={closeModal} onCreate={loadTrees} />}*/}
    </>
  );
};

export {TemplateIndex};
