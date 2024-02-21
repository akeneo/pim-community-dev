import React, {FC} from 'react';
import {useCatalogContext, useProductFamily} from '../../../../../../infrastructure/hooks';
import AttributeWithRecommendation from '../../../../../../domain/AttributeWithRecommendation.interface';
import AttributesList from './AttributesList';
import {
  getAttributesListWithLabels,
  getLevelAttributes,
  isRootProductModel,
  isSimpleProduct,
  sortAttributesList,
} from '../../../../../helper';
import {Evaluation, Product} from '../../../../../../domain';
import {
  FollowAttributeRecommendationHandler,
  FollowAttributesListRecommendationHandler,
} from '../../../../../user-actions';
import {Recommendation} from './Recommendation';
import {ROOT_PRODUCT_MODEL_LEVEL} from '../../../../../constant';

const translate = require('oro/translator');

const buildAttributesListByLevelVariation = (
  criterion: string,
  axis: string,
  evaluation: Evaluation,
  attributes: AttributeWithRecommendation[],
  product: Product,
  locale?: string,
  followAttributeRecommendation?: FollowAttributeRecommendationHandler,
  followAttributesListRecommendation?: FollowAttributesListRecommendationHandler
): JSX.Element[] => {
  return (
    product.meta.variant_navigation
      // @ts-ignore
      .filter((_: any, level) => level <= product.meta.level)
      .map((variant: any, level: number) => {
        return (
          <div
            key={`variant-attributes-list-${level}`}
            className="attributeList"
            data-testid={`attributes-level-${level}`}
          >
            <span>
              {locale && level > 0
                ? variant.axes[locale]
                : translate('pim_enrich.entity.product.module.variant_navigation.common')}
            </span>
            :&thinsp;
            <AttributesList
              criterionCode={criterion}
              attributes={getLevelAttributes(attributes, level, product)}
              axis={axis}
              evaluation={evaluation}
              followAttributeRecommendation={followAttributeRecommendation}
              followAttributesListRecommendation={followAttributesListRecommendation}
            />
          </div>
        );
      })
  );
};

interface RecommendationWithAttributesListProps {
  criterion: string;
  attributes: string[];
  product: Product;
  axis: string;
  evaluation: Evaluation;
  followAttributeRecommendation?: FollowAttributeRecommendationHandler;
  followAttributesListRecommendation?: FollowAttributesListRecommendationHandler;
}

const RecommendationWithAttributesList: FC<RecommendationWithAttributesListProps> = ({
  criterion,
  attributes,
  axis,
  evaluation,
  product,
  followAttributeRecommendation,
  followAttributesListRecommendation,
}) => {
  const {locale} = useCatalogContext();
  const family = useProductFamily();
  const attributesLabels: AttributeWithRecommendation[] = getAttributesListWithLabels(attributes, family, locale);
  const sortedAttributes = sortAttributesList(attributesLabels);

  if (sortedAttributes.length === 0) {
    return <Recommendation type={'not_applicable'} />;
  }

  if (isSimpleProduct(product)) {
    return (
      <AttributesList
        criterionCode={criterion}
        attributes={sortedAttributes}
        axis={axis}
        evaluation={evaluation}
        followAttributeRecommendation={followAttributeRecommendation}
        followAttributesListRecommendation={followAttributesListRecommendation}
      />
    );
  }

  if (isRootProductModel(product)) {
    return (
      <AttributesList
        criterionCode={criterion}
        attributes={getLevelAttributes(sortedAttributes, ROOT_PRODUCT_MODEL_LEVEL, product)}
        axis={axis}
        evaluation={evaluation}
        followAttributeRecommendation={followAttributeRecommendation}
        followAttributesListRecommendation={followAttributesListRecommendation}
      />
    );
  }

  const attributesListByLevelVariation = buildAttributesListByLevelVariation(
    criterion,
    axis,
    evaluation,
    sortedAttributes,
    product,
    locale,
    followAttributeRecommendation,
    followAttributesListRecommendation
  );

  return <div className="CriterionVariantRecommendations">{attributesListByLevelVariation}</div>;
};

export {RecommendationWithAttributesList};
