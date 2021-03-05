import React, {FunctionComponent, useEffect, useState} from 'react';
import useFetchWidgetCategories from '../../../../infrastructure/hooks/Dashboard/useFetchWidgetCategories';
import {Ranks} from '../../../../domain/Rate.interface';
import CategoryModal from '../CategoryModal/CategoryModal';
import {uniqBy as _uniqBy, xorBy as _xorBy} from 'lodash';
import Category from '../../../../domain/Category.interface';
import {redirectToProductGridFilteredByCategory} from '../../../../infrastructure/ProductGridRouter';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {SeeInGrid} from './SeeInGrid';
import {RemoveItem} from './RemoveItem';
import {AddItem} from './AddItem';
import {Cell, HeaderCell, Row, Table} from './Table';
import {Scoring} from 'akeneo-design-system';

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
  const translate = useTranslate();

  const averageScoreByCategories = useFetchWidgetCategories(catalogChannel, catalogLocale, watchedCategories);

  const onSelectCategory = (
    categoryCode: string,
    categoryLabel: string,
    categoryId: string,
    rootCategoryId: string
  ) => {
    const selectedCategory = {
      code: categoryCode,
      label: categoryLabel,
      id: categoryId,
      rootCategoryId: rootCategoryId,
    };
    const categoriesToSelect = _xorBy([selectedCategory], categoriesToWatch, 'code');

    setModalErrorMessage(null);
    if (_uniqBy([...watchedCategories, ...categoriesToSelect], 'code').length > MAX_WATCHED_CATEGORIES) {
      setModalErrorMessage(
        translate('akeneo_data_quality_insights.dqi_dashboard.widgets.category_modal.max_categories_msg', {
          count: `${MAX_WATCHED_CATEGORIES}`,
        })
      );
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
    setWatchedCategories(
      watchedCategories.filter((watchedCategory: Category) => watchedCategory.code !== categoryCode)
    );
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

  const header = (
    <div className="AknSubsection-title AknSubsection-title--glued">
      <span>{translate('pim_enrich.entity.category.plural_label')}</span>
      <AddItem
        add={() => {
          setShowModal(true);
        }}
      >
        {translate('akeneo_data_quality_insights.dqi_dashboard.widgets.add_categories')}
      </AddItem>
    </div>
  );

  const categoryModal = (
    <CategoryModal
      isVisible={showModal}
      onDismissModal={onDismissModal}
      onSelectCategory={onSelectCategory}
      onConfirm={onConfirmCategoriesToWatch}
      selectedCategories={categoriesToWatch.map((category: Category) => category.code)}
      withCheckBox={true}
      subtitle={translate('akeneo_data_quality_insights.dqi_dashboard.widgets.category_modal.subtitle')}
      description={translate('akeneo_data_quality_insights.dqi_dashboard.widgets.category_modal.message')}
      errorMessage={modalErrorMessage}
    />
  );

  if (Object.keys(averageScoreByCategories).length === 0) {
    return (
      <>
        {header}
        <div className="no-family">
          <img src="bundles/pimui/images/illustrations/Product-categories.svg" />
          <p>{translate('akeneo_data_quality_insights.dqi_dashboard.widgets.no_category_helper_msg')}</p>
        </div>
        {categoryModal}
      </>
    );
  }

  return (
    <>
      {header}
      <Table>
        <Row isHeader={true}>
          <HeaderCell>{translate('akeneo_data_quality_insights.dqi_dashboard.widgets.title')}</HeaderCell>
          <HeaderCell align={'center'} width={48}>
            {translate(`akeneo_data_quality_insights.dqi_dashboard.widgets.score`)}
          </HeaderCell>
          <HeaderCell />
          <HeaderCell />
        </Row>
        {Object.entries(averageScoreByCategories).map(
          ([categoryCode, averageScoreRank]: [string, any], index: number) => {
            const category = watchedCategories.find(
              (watchedCategory: Category) => watchedCategory.code === categoryCode
            );
            return (
              category && (
                <Row key={index}>
                  <Cell highlight={true}>{category.label ? category.label : '[' + category.code + ']'}</Cell>
                  <Cell align={'center'}>
                    <Scoring score={averageScoreRank ? Ranks[averageScoreRank] : 'N/A'} />
                  </Cell>
                  <Cell action={true}>
                    <SeeInGrid
                      follow={() =>
                        redirectToProductGridFilteredByCategory(
                          catalogChannel,
                          catalogLocale,
                          category.id,
                          category.rootCategoryId
                        )
                      }
                    />
                  </Cell>
                  <Cell action={true}>
                    <RemoveItem remove={() => onRemoveCategory(categoryCode)} />
                  </Cell>
                </Row>
              )
            );
          }
        )}
      </Table>
      {categoryModal}
    </>
  );
};

export default CategoryWidget;
