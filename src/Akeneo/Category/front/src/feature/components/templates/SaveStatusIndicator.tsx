import {CheckRoundIcon, DangerIcon, EditIcon, LoaderIcon} from 'akeneo-design-system';
import {useSaveStatusContext} from '../../hooks/useSaveStatusContext';
import {Status} from '../providers/SaveStatusProvider';
import {useTranslate} from '@akeneo-pim-community/shared';

export const SaveStatusIndicator = () => {
  const saveStatus = useSaveStatusContext();
  const translate = useTranslate();

  switch (saveStatus.globalStatus) {
    case Status.EDITING:
      return (
        <div>
          <EditIcon color="#a1a9b7" size={24} />
          <p>{translate('akeneo.category.template.auto-save.editing')}</p>
        </div>
      );
    case Status.SAVING:
      return (
        <div>
          <LoaderIcon color="#a1a9b7" size={24} />
          <p>{translate('akeneo.category.template.auto-save.saving')}</p>
        </div>
      );
    case Status.ERRORS:
      return (
        <div>
          <DangerIcon color="#f9b53f" size={24} />
          <p>{translate('akeneo.category.template.auto-save.errors')}</p>
        </div>
      );
    case Status.SAVED:
    default:
      return (
        <div>
          <CheckRoundIcon color="#67b373" size={24} />
          <p>{translate('akeneo.category.template.auto-save.saved')}</p>
        </div>
      );
  }
};
