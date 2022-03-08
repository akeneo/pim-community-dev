import React, {FC, useCallback} from 'react';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {roughCount, computeTipMessage, getProgressBarLevel} from '../../../helper/Dashboard/KeyIndicator';
import {
  Tip,
  KeyIndicatorTips,
  CountsByProductType,
  KeyIndicatorExtraData,
  areAllCountsZero,
  computePercent,
} from '../../../../domain';
import {useGetKeyIndicatorTips} from '../../../../infrastructure/hooks/Dashboard/UseKeyIndicatorTips';
import {useDashboardContext} from '../../../context/DashboardContext';
import {ProgressBar} from 'akeneo-design-system';
import {FollowKeyIndicatorResultHandler} from '../../../user-actions';
import {ProductType} from '../../../../domain/Product.interface';
import {MarkersMapping, messageBuilder} from './messageBuilder';
import {TextWithLink} from './TextWithLink';

type Props = {
  type: string;
  counts: CountsByProductType;
  title: string;
  followResults?: FollowKeyIndicatorResultHandler;
  extraData?: KeyIndicatorExtraData;
};

const KeyIndicator: FC<Props> = ({children, type, counts, title, followResults, extraData}) => {
  const translate = useTranslate();
  const tips: KeyIndicatorTips = useGetKeyIndicatorTips(type);
  const userContext = useUserContext();
  const {category, familyCode} = useDashboardContext();

  const handleClickOnCount = useCallback(
    (productType: ProductType) => (event: React.SyntheticEvent<HTMLButtonElement>) => {
      event.stopPropagation();

      followResults?.(
        userContext.get('catalogScope'),
        userContext.get('catalogLocale'),
        productType,
        familyCode,
        category?.id || null,
        category?.rootCategoryId || null,
        extraData || undefined
      );
    },
    [userContext, familyCode, category, extraData]
  );

  // if (title === undefined) {
  //   return <></>;
  // }

  if (areAllCountsZero(counts)) {
    return (
      <Container>
        <Icon>{children}</Icon>
        <Content>
          <ProgressBar
            size="small"
            title={translate(title)}
            progressLabel={'\u00a0'}
            light={true}
            level={'tertiary'}
            percent={0}
          />
          <Text>{translate(`akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.${type}.no_data`)}</Text>
        </Content>
      </Container>
    );
  }

  const {
    products: {totalGood: nbProductsOK, totalToImprove: nbProductsKO},
    product_models: {totalGood: nbProductModelsOK, totalToImprove: nbProductModelsKO},
  } = counts;

  const percentOK = computePercent(nbProductsOK, nbProductsKO);

  const tip: Tip = computeTipMessage(tips, percentOK);

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

  const productsButton = <button onClick={handleClickOnCount('product')}>{roughCountProductsKOText}</button>;

  const productModelsButton = (
    <button onClick={handleClickOnCount('product_model')}>{roughCountProductModelsKOText}</button>
  );

  let messageSourceI18nKey = 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.entities_to_work_on';
  let markersMapping: MarkersMapping;

  if (nbProductsKO > 0) {
    if (nbProductModelsKO > 0) {
      messageSourceI18nKey = 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.entities_to_work_on_2_kinds';
      markersMapping = {
        '[A]': productsButton,
        '[B]': productModelsButton,
      };
    } else {
      markersMapping = {
        '[A]': productsButton,
      };
    }
  } else {
    markersMapping = {
      '[A]': productModelsButton,
    };
  }

  const entitiesToWorkOn = messageBuilder(markersMapping)(translate(messageSourceI18nKey));

  return (
    <Container>
      <Icon>{children}</Icon>
      <Content>
        <ProgressBar
          level={getProgressBarLevel(percentOK)}
          light={percentOK === 0 || (percentOK >= 50 && percentOK < 80)}
          percent={percentOK}
          progressLabel={percentOK + '%'}
          size="small"
          title={translate(title)}
        />
        <Text>
          {entitiesToWorkOn}
          &nbsp;
          <TextWithLink
            dangerouslySetInnerHTML={{
              __html: translate(tip.message, {link: tip.link || ''}),
            }}
          />
        </Text>
      </Content>
    </Container>
  );
};

const Container = styled.div`
  flex: 1 0 50%;
  display: flex;
  margin: 24px 0 0 0;
  max-width: 50%;

  :nth-child(odd) {
    padding-right: 20px;
  }
  :nth-child(even) {
    padding-left: 20px;
  }
`;

const Icon = styled.div`
  border-right: 1px solid ${({theme}) => theme.color.grey80};
  min-width: 64px;
  padding-top: 18px;
  height: 60px;
  text-align: center;
  margin-right: 20px;
  color: ${({theme}) => theme.color.grey100};
`;

const Content = styled.div`
  flex-grow: 1;
`;

const Text = styled.div`
  color: ${({theme}) => theme.color.grey100};
  margin-top: 10px;
`;

export {KeyIndicator};
