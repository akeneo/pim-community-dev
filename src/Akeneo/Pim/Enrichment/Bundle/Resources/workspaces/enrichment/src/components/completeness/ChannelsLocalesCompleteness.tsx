import React, {FC} from 'react';
import {ProgressBar} from 'akeneo-design-system';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {ChannelsLocalesCompletenessRatios} from "../../models";

type Props = {
  channelsLocalesRatios: ChannelsLocalesCompletenessRatios | null;
};

const ChannelsLocalesCompleteness: FC<Props> = ({channelsLocalesRatios}) => {
  const translate = useTranslate();

  return (
    <CompletenessContainer>
      {channelsLocalesRatios !== null &&
        Object.entries(channelsLocalesRatios).map(([channelLabel, {channelRatio, localesRatios}]) => {
          return (
            <ChannelCompletenessContainer key={channelLabel}>
              <div>
                <ProgressBar
                  title={channelLabel}
                  size="large"
                  level={getProgressbarLevel(channelRatio)}
                  percent={channelRatio}
                  progressLabel={`${channelRatio} %`}
                />
                <ProgressMessage>{translate(getProgressMessageByChannelRatio(channelRatio))}</ProgressMessage>
              </div>
              <LocalesCompletenessContainer>
                {Object.entries(localesRatios).map(([localeLabel, localeRatio]: [string, number]) => {
                  return (
                    <ProgressBar
                      title={localeLabel}
                      size="small"
                      level={getProgressbarLevel(localeRatio)}
                      percent={localeRatio}
                      progressLabel={`${localeRatio} %`}
                      key={`${channelLabel}-${localeLabel}`}
                    />
                  );
                })}
              </LocalesCompletenessContainer>
            </ChannelCompletenessContainer>
          );
        })}
    </CompletenessContainer>
  );
};

const getProgressMessageByChannelRatio = (channelRatio: number): string => {
  return channelRatio < 1
    ? 'pim_dashboard.widget.completeness.progress_messages.message1'
    : channelRatio < 50
    ? 'pim_dashboard.widget.completeness.progress_messages.message2'
    : channelRatio < 100
    ? 'pim_dashboard.widget.completeness.progress_messages.message3'
    : 'pim_dashboard.widget.completeness.progress_messages.message4';
};

const getProgressbarLevel = (score: number) => {
  return score < 100 ? 'warning' : 'primary';
};

const CompletenessContainer = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  column-gap: 40px;
  row-gap: 40px;
`;

const ChannelCompletenessContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
`;

const ProgressMessage = styled.div`
  margin-top: 10px;
`;

const LocalesCompletenessContainer = styled.div`
  gap: 30px;
  display: flex;
  flex-direction: column;
`;

export {ChannelsLocalesCompleteness};
