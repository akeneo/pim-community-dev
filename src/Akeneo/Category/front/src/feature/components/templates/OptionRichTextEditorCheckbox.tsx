import {useFeatureFlags, useTranslate} from '@akeneo-pim-community/shared';
import {Checkbox} from 'akeneo-design-system';
import {useSaveStatus} from 'feature/hooks/useSaveStatus';
import {useQueryClient} from 'react-query';
import {useUpdateTemplateAttribute} from '../../hooks/useUpdateTemplateAttribute';
import {Attribute} from '../../models';
import {Status} from '../providers/SaveStatusProvider';

type Props = {
  attribute: Attribute;
};

export const OptionRichTextEditorCheckbox = ({attribute}: Props) => {
  const featureFlag = useFeatureFlags();
  const translate = useTranslate();
  const queryClient = useQueryClient();

  const {handleStatusListChange} = useSaveStatus();
  const saveStatusId = attribute.uuid + '_option_richtext';

  const mutation = useUpdateTemplateAttribute(attribute.template_uuid, attribute.uuid);

  const handleRichTextAreaChange = () => {
    handleStatusListChange(saveStatusId, Status.SAVING);
    mutation.mutate(
      {isRichTextArea: !(attribute.type === 'richtext')},
      {
        onSuccess: () => {
          handleStatusListChange(saveStatusId, Status.SAVED);
          queryClient.invalidateQueries(['get-template', attribute.template_uuid]);
        },
      }
    );
  };

  if (false === ['textarea', 'richtext'].includes(attribute.type)) {
    return null;
  }

  return (
    <Checkbox
      checked={attribute.type === 'richtext'}
      onChange={handleRichTextAreaChange}
      readOnly={!featureFlag.isEnabled('category_update_template_attribute')}
    >
      {translate('akeneo.category.template.attribute.settings.options.rich_text')}
    </Checkbox>
  );
};
