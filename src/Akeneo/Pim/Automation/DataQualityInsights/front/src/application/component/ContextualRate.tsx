import React, {FunctionComponent} from 'react';
import {Rates} from "../../domain";
import {get as _get} from 'lodash';
import Rate from "./Rate";

interface ContextualRateProps {
  rates: Rates;
  channel: string;
  locale: string;
}

export const ContextualRate: FunctionComponent<ContextualRateProps> = ({rates, channel, locale}) => {
  let rate = _get(rates, [channel, locale]);
  return (
    <Rate value={rate} />
  );
};
