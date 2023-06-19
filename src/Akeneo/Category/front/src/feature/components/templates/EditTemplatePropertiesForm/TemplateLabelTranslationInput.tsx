import {Field, Helper, TextInput} from 'akeneo-design-system';
import {useQueryClient} from 'react-query';
import {useSaveStatus} from '../../../hooks/useSaveStatus';
import {useUpdateTemplateProperties} from '../../../hooks/useUpdateTemplateProperties';
import {Template} from '../../../models';
import {BadRequestError} from '../../../tools/apiFetch';
import {useDebounceCallback} from '../../../tools/useDebounceCallback';
import {Status} from '../../providers/SaveStatusProvider';
import {useTemplateForm} from '../../providers/TemplateFormProvider';

const useFormData = (template: Template, localeCode: string) => {
  const [state] = useTemplateForm();

  const formData = state.properties.labels?.[localeCode];

  if (undefined === formData) {
    return {
      value: template.labels[localeCode] || '',
      errors: [],
    };
  }

  return formData;
};

type Props = {
  locale: {
    code: string;
    label: string;
  };
  template: Template;
};

export const TemplateLabelTranslationInput = ({template, locale}: Props) => {
  const queryClient = useQueryClient();

  const {handleStatusListChange} = useSaveStatus();
  const saveStatusId = template.uuid + '_label_translation_' + locale.code;

  const [, dispatch] = useTemplateForm();

  const mutation = useUpdateTemplateProperties(template.uuid);
  const debouncedUpdateTemplateLabel = useDebounceCallback(async (value: string) => {
    try {
      handleStatusListChange(saveStatusId, Status.SAVING);
      await mutation.mutateAsync({labels: {[locale.code]: value}});
      await queryClient.invalidateQueries(['get-template', template.uuid]);
      dispatch({
        type: 'template_label_translation_saved',
        payload: {localeCode: locale.code, value},
      });
      handleStatusListChange(saveStatusId, Status.SAVED);
    } catch (error) {
      if (error instanceof BadRequestError) {
        dispatch({
          type: 'save_template_label_translation_failed',
          payload: {localeCode: locale.code, errors: error.data.labels[locale.code]},
        });
        handleStatusListChange(saveStatusId, Status.ERRORS);
      } else {
        throw error;
      }
    }
  }, 300);

  const handleChange = async (value: string) => {
    dispatch({
      type: 'template_label_translation_changed',
      payload: {localeCode: locale.code, value},
    });
    debouncedUpdateTemplateLabel(value);
    handleStatusListChange(saveStatusId, Status.EDITING);
  };

  const formData = useFormData(template, locale.code);

  return (
    <Field key={locale.code} locale={locale.code} label={locale.label}>
      <TextInput value={formData.value} onChange={handleChange} />
      {formData.errors.map(message => (
        <Helper level="error" key={message}>
          {message}
        </Helper>
      ))}
    </Field>
  );
};
