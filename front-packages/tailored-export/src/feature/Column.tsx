import {Button, TagIcon} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/shared';

const Column = ({jobCode}: {jobCode: string}) => {
  const translate = useTranslate();
  const notify = useNotify();

  const handleClick = () => {
    notify(
      NotificationLevel.SUCCESS,
      'Coucou',
      <span>
        dddsd <em>fewfewf</em>
      </span>,
      <TagIcon />
    );
  };

  return (
    <Button level="secondary" onClick={handleClick}>
      {translate('pim_common.edit')}: {jobCode}!
    </Button>
  );
};

export {Column};
