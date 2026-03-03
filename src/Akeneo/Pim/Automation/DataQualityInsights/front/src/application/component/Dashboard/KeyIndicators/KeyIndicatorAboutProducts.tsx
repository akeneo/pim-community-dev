import React, {FC, useCallback} from 'react';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {computeTipMessage} from '../../../helper/Dashboard/KeyIndicator';
import {
  Tip,
  KeyIndicatorTips,
  CountsByProductType,
  KeyIndicatorExtraData,
  areAllCountsZero,
  computePercent,
  IntegerPercent,
  KeyIndicatorProducts,
  makeCounts,
} from '@akeneo-pim-community/data-quality-insights/src/domain';
import {useGetKeyIndicatorTips} from '../../../../infrastructure/hooks/Dashboard/UseKeyIndicatorTips';
import {useDashboardContext} from '../../../context/DashboardContext';
import {ProductsKeyIndicatorLinkCallback} from '../../../user-actions';
import {ProductType} from '../../../../domain/Product.interface';
import {ProductMessageBuilder} from './ProductMessageBuilder';
import {KeyIndicatorNoData} from './KeyIndicatorNoData';
import {TextWithLink, Text} from './styled';
import {KeyIndicatorBase} from './KeyIndicatorBase';

const defaultCounts = makeCounts();

type Props = {
  type: KeyIndicatorProducts;
  counts: CountsByProductType;
  title: string;
  followResults: ProductsKeyIndicatorLinkCallback;
  extraData?: KeyIndicatorExtraData;
};

export const KeyIndicatorAboutProducts: FC<Props> = ({children, type, counts, title, followResults, extraData}) => {
  const translate = useTranslate();
  const tips: KeyIndicatorTips = useGetKeyIndicatorTips(type);
  const userContext = useUserContext();
  const {category, familyCode} = useDashboardContext();

  const handleClickOnCount = useCallback(
    (entityType: ProductType) => (event: React.SyntheticEvent<HTMLElement>) => {
      event.stopPropagation();

      followResults(
        userContext.get('catalogScope'),
        userContext.get('catalogLocale'),
        entityType,
        familyCode,
        category?.id || null,
        category?.rootCategoryId || null,
        extraData || undefined
      );
    },
    [userContext, familyCode, category, extraData]
  );

  if (areAllCountsZero(counts)) {
    return (
      <KeyIndicatorNoData type={type} title={title}>
        {children}
      </KeyIndicatorNoData>
    );
  }

  let percentOK: IntegerPercent;
  let entitiesToWorkOn: JSX.Element;

  const {products: {totalGood, totalToImprove} = defaultCounts} = counts;

  percentOK = computePercent(totalGood, totalToImprove);

  entitiesToWorkOn = (
    <ProductMessageBuilder
      counts={counts}
      onClickOnProducts={handleClickOnCount('product')}
      onClickOnProductModels={handleClickOnCount('product_model')}
    />
  );

  const tip: Tip = computeTipMessage(tips, percentOK);

  return (
    <KeyIndicatorBase percentOK={percentOK} titleI18nKey={title} icon={children}>
      <Text>
        {entitiesToWorkOn}
        <TextWithLink
          dangerouslySetInnerHTML={{
            __html: translate(tip.message, {link: tip.link || ''}),
          }}
        />
      </Text>
    </KeyIndicatorBase>
  );
};
