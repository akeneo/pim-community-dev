import React, {FC, useCallback} from 'react';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {Breadcrumb} from 'akeneo-design-system';
import {PimView, useRouter, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {EmptyCategoryTreeList} from '../../components';

const CategoriesIndex: FC = () => {
  const router = useRouter();
  const translate = useTranslate();

  const followSettingsIndex = () => router.redirect(router.generate('pim_enrich_attribute_index'));

  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step onClick={followSettingsIndex}>{translate('pim_menu.tab.settings')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_enrich.entity.category.plural_label')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Title>{translate('pim_enrich.entity.category.page_title.index', {count: 0}, 0)}</PageHeader.Title>
      </PageHeader>
      <PageContent>
        <EmptyCategoryTreeList />
      </PageContent>
    </>
  );
};

export {CategoriesIndex};
