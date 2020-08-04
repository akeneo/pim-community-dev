import React, {FunctionComponent, useEffect, useState} from "react";
import useFetchWidgetCategories from "../../../../infrastructure/hooks/Dashboard/useFetchWidgetCategories";
import Rate from '@akeneo-pim-community/data-quality-insights/src/application/component/Rate';
import {Ranks} from "../../../../domain/Rate.interface";
import CategoryModal from "../CategoryModal/CategoryModal";
import {uniqBy as _uniqBy, xorBy as _xorBy} from 'lodash';
import Category from "../../../../domain/Category.interface";
import {redirectToProductGridFilteredByCategory} from "../../../../infrastructure/ProductGridRouter";
import {useAxesContext} from "@akeneo-pim-community/data-quality-insights/src/application/context/AxesContext";

const __ = require('oro/translator');

const MAX_WATCHED_CATEGORIES = 20;
const LOCAL_STORAGE_KEY = 'data-quality-insights:dashboard:widgets:categories';

interface CategoryWidgetProps {
  catalogLocale: string;
  catalogChannel: string;
}

const CategoryWidget: FunctionComponent<CategoryWidgetProps> = ({catalogChannel, catalogLocale}) => {
  const [watchedCategories, setWatchedCategories] = useState<Category[]>([]);
  const [categoriesToWatch, setCategoriesToWatch] = useState<Category[]>([]);
  const [showModal, setShowModal] = useState<boolean>(false);
  const [modalErrorMessage, setModalErrorMessage] = useState<string | null>(null);
  const axesContext = useAxesContext();

  const ratesByCategory = useFetchWidgetCategories(catalogChannel, catalogLocale, watchedCategories);

  const onSelectCategory = (categoryCode: string, categoryLabel: string, categoryId: string, rootCategoryId: string) => {
    const selectedCategory = {'code': categoryCode, 'label': categoryLabel, 'id': categoryId, 'rootCategoryId': rootCategoryId};
    const categoriesToSelect = _xorBy([selectedCategory], categoriesToWatch, 'code');

    setModalErrorMessage(null);
    if (_uniqBy([...watchedCategories, ...categoriesToSelect], 'code').length > MAX_WATCHED_CATEGORIES) {
      setModalErrorMessage(__('akeneo_data_quality_insights.dqi_dashboard.widgets.category_modal.max_categories_msg', {count: MAX_WATCHED_CATEGORIES}));
    }
    setCategoriesToWatch(categoriesToSelect);
  };

  const onConfirmCategoriesToWatch = () => {
    setWatchedCategories(_uniqBy([...watchedCategories, ...categoriesToWatch], 'code'));
    setCategoriesToWatch([]);
    setShowModal(false);
  };

  const onDismissModal = () => {
    setShowModal(false);
    setModalErrorMessage(null);
    setCategoriesToWatch([]);
  };

  const onRemoveCategory = (categoryCode: string) => {
      setWatchedCategories(watchedCategories.filter((watchedCategory: Category) => watchedCategory.code !== categoryCode));
  };

  useEffect(() => {
    let storedCategories = localStorage.getItem(LOCAL_STORAGE_KEY);
    if (storedCategories) {
      setWatchedCategories(JSON.parse(storedCategories));
    }
  }, []);

  useEffect(() => {
    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(watchedCategories));
  }, [watchedCategories]);

  const header = <div className="AknSubsection-title AknSubsection-title--glued">
    <span>{__('pim_enrich.entity.category.plural_label')}</span>
    <div className="AknButton AknButton--micro" onClick={() => {setShowModal(true)}}>
      {__('akeneo_data_quality_insights.dqi_dashboard.widgets.add_categories')}
    </div>
  </div>;

  const categoryModal =
    <CategoryModal
      isVisible={showModal}
      onDismissModal={onDismissModal}
      onSelectCategory={onSelectCategory}
      onConfirm={onConfirmCategoriesToWatch}
      selectedCategories={categoriesToWatch.map((category: Category) => category.code)}
      withCheckBox={true}
      subtitle={__('akeneo_data_quality_insights.dqi_dashboard.widgets.category_modal.subtitle')}
      description={__('akeneo_data_quality_insights.dqi_dashboard.widgets.category_modal.message')}
      errorMessage={modalErrorMessage}
    />;

  if (Object.keys(ratesByCategory).length === 0) {
    return (
      <>
        {header}
        <div className="no-family">
          <img src="bundles/pimui/images/illustrations/Product-categories.svg"/>
          <p>{__('akeneo_data_quality_insights.dqi_dashboard.widgets.no_category_helper_msg')}</p>
        </div>
        {categoryModal}
      </>
    );
  }

  return (
    <>
      {header}
      <table className="AknGrid AknGrid--unclickable">
        <tbody className="AknGrid-body">
          <tr>
            <th className="AknGrid-headerCell">{__('akeneo_data_quality_insights.dqi_dashboard.widgets.title')}</th>
            <th className="AknGrid-headerCell AknDataQualityInsightsGrid-axis-rate">{__(`akeneo_data_quality_insights.product_evaluation.axis.enrichment.title`)}</th>
            {axesContext.axes.includes('consistency') &&
              <th className="AknGrid-headerCell AknDataQualityInsightsGrid-axis-rate">{__(`akeneo_data_quality_insights.product_evaluation.axis.consistency.title`)}</th>
            }
            <th className="AknGrid-headerCell AknDataQualityInsightsGrid-axis-rate"> </th>
            <th className="AknGrid-headerCell AknDataQualityInsightsGrid-axis-rate"> </th>
          </tr>
          {Object.entries(ratesByCategory).map(([categoryCode, ratesByAxis]: [string, any], index: number) => {
            const category = watchedCategories.find((watchedCategory: Category) => watchedCategory.code === categoryCode);
            return category && (
              <tr key={index} className="AknGrid-bodyRow">
                <td className="AknGrid-bodyCell AknGrid-bodyCell--highlight categoryName">
                  {category.label ? category.label : '[' + category.code + ']'}
                </td>
                <td className="AknGrid-bodyCell AknDataQualityInsightsGrid-axis-rate">
                  <Rate value={ratesByAxis.enrichment ? Ranks[ratesByAxis.enrichment] : null}/>
                </td>
                {axesContext.axes.includes('consistency') &&
                  <td className="AknGrid-bodyCell AknDataQualityInsightsGrid-axis-rate">
                    <Rate value={ratesByAxis.consistency ? Ranks[ratesByAxis.consistency] : null}/>
                  </td>
                }
                <td className="AknGrid-bodyCell AknGrid-bodyCell--actions">
                  <div className="AknButton AknButton--micro" onClick={() => redirectToProductGridFilteredByCategory(catalogChannel, catalogLocale, category.id, category.rootCategoryId)}>
                    {__('akeneo_data_quality_insights.dqi_dashboard.widgets.see_in_grid')}
                  </div>
                </td>
                <td className="AknGrid-bodyCell AknGrid-bodyCell--actions">
                  <img style={{cursor: "pointer"}} width="16" src="/bundles/pimui/images/icon-delete-slategrey.svg" onClick={() => onRemoveCategory(categoryCode)} />
                </td>
              </tr>
            )
          })}
        </tbody>
      </table>
      {categoryModal}
    </>
  );
};

export default CategoryWidget;
