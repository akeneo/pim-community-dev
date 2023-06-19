import {useTranslate} from '@akeneo-pim-community/shared';
import {Checkbox} from 'akeneo-design-system';
import {useState} from 'react';
import {useQueryClient} from 'react-query';
import {useSaveStatus} from '../../../hooks/useSaveStatus';
import {useUpdateTemplateAttribute} from '../../../hooks/useUpdateTemplateAttribute';
import {Attribute} from '../../../models';
import {Status} from '../../providers/SaveStatusProvider';

type Props = {
  attribute: Attribute;
};

export const AttributeOptionRichTextCheckbox = ({attribute}: Props) => {
  const translate = useTranslate();
  const queryClient = useQueryClient();

  const {handleStatusListChange} = useSaveStatus();
  const saveStatusId = attribute.uuid + '_option_richtext';

  const mutation = useUpdateTemplateAttribute(attribute.template_uuid, attribute.uuid);
  const [isSaving, setIsSaving] = useState(false);

  const handleRichTextAreaChange = async () => {
    setIsSaving(true);
    handleStatusListChange(saveStatusId, Status.SAVING);
    await mutation.mutateAsync({isRichTextArea: !(attribute.type === 'richtext')});
    await queryClient.invalidateQueries(['get-template', attribute.template_uuid]);
    handleStatusListChange(saveStatusId, Status.SAVED);
    setIsSaving(false);
  };

  if (false === ['textarea', 'richtext'].includes(attribute.type)) {
    return null;
  }

  return (
    <Checkbox checked={attribute.type === 'richtext'} onChange={handleRichTextAreaChange} readOnly={isSaving}>
      {translate('akeneo.category.template.attribute.settings.options.rich_text')}
    </Checkbox>
  );
};
