import React, {FC} from 'react';
import {useTranslate} from "@akeneo-pim-community/legacy-bridge";
import styled from "styled-components";
import {ProgressBar} from "./ProgressBar";
import {
  computeProductsNumberToWorkOn,
  computeTipMessage,
  getProgressBarLevel
} from "../../../helper/Dashboard/KeyIndicator";
import {Tip, KeyIndicatorTips} from "../../../../domain";
import {useGetKeyIndicatorTips} from "../../../../infrastructure/hooks/Dashboard/UseKeyIndicatorTips";

type Props = {
  type: string;
  ratioGood?: number;
  totalToImprove?: number;
  title?: string;
  resultsMessage?: string;
  followResults?: () => void;
};

const KeyIndicator: FC<Props> = ({children, type, ratioGood, totalToImprove, title, resultsMessage, followResults}) => {
  const translate = useTranslate();
  const tips: KeyIndicatorTips = useGetKeyIndicatorTips(type);

  if (ratioGood === undefined || totalToImprove === undefined || title === undefined) {
    return <></>;
  }

  const tip: Tip = computeTipMessage(tips, ratioGood);

  const productsNumberToWorkOn: number = computeProductsNumberToWorkOn(totalToImprove);

  const handleOnClickOnProductsNumber = (event: any) => {
    event.stopPropagation();
    if (event.target.tagName === 'BUTTON' && followResults) {
      followResults();
    }
  };

  return (
    <Container>
      <Icon>{children}</Icon>
      <Content>
        <ProgressBar
          level={getProgressBarLevel(ratioGood)}
          light={ratioGood === 0 || (ratioGood >= 50 && ratioGood < 80)}
          percent={ratioGood}
          progressLabel={Math.round(ratioGood) + '%'}
          size="small"
          title={translate(title)}
        />
        <Text>
          {
            totalToImprove > 0 && resultsMessage &&
            <TextWithLink onClickCapture={(event: any) => handleOnClickOnProductsNumber(event)} dangerouslySetInnerHTML={{
              __html: translate(
                resultsMessage,
                {count: productsNumberToWorkOn.toString()},
                productsNumberToWorkOn
              )
            }}/>
          }
            &nbsp;
          <TextWithLink dangerouslySetInnerHTML={{
            __html: translate(tip.message, {link: tip.link || ''})
          }}/>
        </Text>
      </Content>
    </Container>
  );
}

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
`;

const Content = styled.div`
  flex-grow: 1;
`;

const Text = styled.div`
  color: ${({theme}) => theme.color.grey100};
  margin-top: 10px;
`;

const TextWithLink = styled.span`
  a, button {
    color: ${({theme}) => theme.color.purple100};
    text-decoration: underline ${({theme}) => theme.color.purple100};
    cursor: pointer;
    border: none;
    background: none;
    padding: 0;
    margin: 0;
    
    :focus {
      outline: none;
    }
  }
`;

export {KeyIndicator};
