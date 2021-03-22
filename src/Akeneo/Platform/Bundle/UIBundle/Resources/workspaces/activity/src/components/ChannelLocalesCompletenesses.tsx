import React, {FC} from 'react';
import {ProgressBar} from 'akeneo-design-system';
import styled from 'styled-components';
import {ChannelsLocalesCompletenesses} from '../domain';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type ChannelLocalesCompletenessesProps = {
  data: ChannelsLocalesCompletenesses;
};

const ChannelLocalesCompletenesses: FC<ChannelLocalesCompletenessesProps> = ({data}) => {
  const translate = useTranslate();

  return (
    <CompletenessContainer>
      {data !== null &&
        Object.entries(data).map(([channelLabel, {channelRatio, locales}]) => {
          return (
            <ChannelCompleteness key={channelLabel}>
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
              <LocaleCompleteness>
                {Object.entries(locales).map(([localeLabel, localeRatio]: [string, number]) => {
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
              </LocaleCompleteness>
            </ChannelCompleteness>
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

const ChannelCompleteness = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
`;

const ProgressMessage = styled.div`
  margin-top: 10px;
`;

const LocaleCompleteness = styled.div`
  gap: 30px;
  display: flex;
  flex-direction: column;
`;

export {ChannelLocalesCompletenesses, ChannelsLocalesCompletenesses};
