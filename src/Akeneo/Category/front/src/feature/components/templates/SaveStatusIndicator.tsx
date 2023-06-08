import {CheckRoundIcon, DangerIcon, EditIcon, LoaderIcon} from 'akeneo-design-system';
import {useSaveStatusContext} from '../../hooks/useSaveStatusContext';
import {Status} from '../providers/SaveStatusProvider';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

export const SaveStatusIndicator = () => {
  const saveStatus = useSaveStatusContext();
  const translate = useTranslate();

  switch (saveStatus.globalStatus) {
    case Status.EDITING:
      return (
        <IndicatorContainer>
          <EditIcon color="#a1a9b7" size={24} />
          <TextGrey>{translate('akeneo.category.template.auto-save.editing')}</TextGrey>
        </IndicatorContainer>
      );
    case Status.SAVING:
      return (
        <IndicatorContainer>
          <LoaderIcon color="#a1a9b7" size={24} />
          <TextGrey>{translate('akeneo.category.template.auto-save.saving')}</TextGrey>
        </IndicatorContainer>
      );
    case Status.ERRORS:
      return (
        <IndicatorContainer>
          <DangerIcon color="#f9b53f" size={24} />
          <TextBlack>{translate('akeneo.category.template.auto-save.errors')}</TextBlack>
        </IndicatorContainer>
      );
    case Status.SAVED:
    default:
      return (
        <IndicatorContainer>
          <CheckRoundIcon color="#67b373" size={24} />
          <TextBlack>{translate('akeneo.category.template.auto-save.saved')}</TextBlack>
        </IndicatorContainer>
      );
  }
};

const IndicatorContainer = styled.div`
  display: flex;
  flex-direction: row;
  align-items: center;
  margin-right: 20px;
`;

const TextBlack = styled.p`
  margin-left: 10px;
  margin-right: 10px;
  color: #11324d;
`;

const TextGrey = styled.p`
  margin-left: 10px;
  margin-right: 10px;
  color: #67768a;
`;
