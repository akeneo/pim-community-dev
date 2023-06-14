import {useFeatureFlags, useTranslate} from '@akeneo-pim-community/shared';
import {Checkbox} from 'akeneo-design-system';
import {useSaveStatus} from 'feature/hooks/useSaveStatus';
import {useQueryClient} from 'react-query';
import {useUpdateTemplateAttribute} from '../../hooks/useUpdateTemplateAttribute';
import {Attribute} from '../../models';
import {Status} from '../providers/SaveStatusProvider';
import {useState} from 'react';

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
  const [isSaving, setIsSaving] = useState(false);

  const handleRichTextAreaChange = () => {
    setIsSaving(true);
    handleStatusListChange(saveStatusId, Status.SAVING);
    mutation.mutate(
      {isRichTextArea: !(attribute.type === 'richtext')},
      {
        onSuccess: async () => {
          await queryClient.invalidateQueries(['get-template', attribute.template_uuid]);
          setIsSaving(false);
          handleStatusListChange(saveStatusId, Status.SAVED);
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
      readOnly={isSaving || !featureFlag.isEnabled('category_update_template_attribute')}
    >
      {translate('akeneo.category.template.attribute.settings.options.rich_text')}
    </Checkbox>
  );
};
