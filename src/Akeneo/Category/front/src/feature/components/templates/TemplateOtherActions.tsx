import {useTranslate} from '@akeneo-pim-community/shared';
import {Dropdown, IconButton, MoreIcon, useBooleanState} from 'akeneo-design-system';

type Props = {
  onDeactivateTemplate: () => void;
};

export const TemplateOtherActions = ({onDeactivateTemplate}: Props) => {
  const translate = useTranslate();

  const [isOpen, open, close] = useBooleanState(false);

  const handleDeactivateTemplate = () => {
    close();
    onDeactivateTemplate();
  };

  return (
    <Dropdown>
      <IconButton
        icon={<MoreIcon />}
        onClick={open}
        ghost="borderless"
        level="tertiary"
        title={translate('akeneo.category.other_actions')}
      />
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('akeneo.category.other_actions')}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            <Dropdown.Item onClick={handleDeactivateTemplate}>
              {translate('akeneo.category.template.deactivate.deactivate_template')}
            </Dropdown.Item>
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};
