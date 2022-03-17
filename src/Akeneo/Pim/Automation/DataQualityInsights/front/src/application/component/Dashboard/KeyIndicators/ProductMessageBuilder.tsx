import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {CountsByProductType} from '../../../../domain';
import {roughCount} from '../../../helper/Dashboard/KeyIndicator';
import {MarkersMapping, messageBuilder} from './messageBuilder';

interface Props {
  counts: CountsByProductType;
  onClickOnProducts: (event: React.SyntheticEvent<HTMLElement>) => void;
  onClickOnProductModels: (event: React.SyntheticEvent<HTMLElement>) => void;
}

export const ProductMessageBuilder = (props: Props) => {
  const {counts, onClickOnProducts, onClickOnProductModels} = props;

  const translate = useTranslate();

  const {
    products: {totalToImprove: nbProductsKO},
    product_models: {totalToImprove: nbProductModelsKO},
  } = counts;

  const roughCountProductsKO: number = roughCount(nbProductsKO);
  const roughCountProductModelsKO: number = roughCount(nbProductModelsKO);

  const roughCountProductsKOText = translate(
    'akeneo_data_quality_insights.dqi_dashboard.key_indicators.products',
    {count: roughCountProductsKO.toString()},
    roughCountProductsKO
  );
  const roughCountProductModelsKOText = translate(
    'akeneo_data_quality_insights.dqi_dashboard.key_indicators.product_models',
    {count: roughCountProductModelsKO.toString()},
    roughCountProductModelsKO
  );

  const productsButton = <button onClick={onClickOnProducts}>{roughCountProductsKOText}</button>;

  const productModelsButton = <button onClick={onClickOnProductModels}>{roughCountProductModelsKOText}</button>;

  let messageSourceI18nKey =
    nbProductsKO > 0 && nbProductModelsKO > 0
      ? 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.entities_to_work_on_2_kinds'
      : 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.entities_to_work_on';

  const [buttonA, buttonB] = [
    nbProductsKO > 0 ? productsButton : undefined,
    nbProductModelsKO > 0 ? productModelsButton : undefined,
  ].filter(Boolean);

  const markersMapping: MarkersMapping = {
    '<button_a/>': buttonA,
    '<button_b/>': buttonB,
  };

  return nbProductsKO > 0 || nbProductModelsKO > 0 ? (
    <>{messageBuilder(markersMapping)(translate(messageSourceI18nKey))} </>
  ) : (
    <></>
  );
};
