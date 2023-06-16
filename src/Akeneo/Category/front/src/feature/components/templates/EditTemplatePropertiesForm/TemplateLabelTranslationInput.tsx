import {Field, Helper, TextInput} from 'akeneo-design-system';
import {useMutation, useQueryClient} from 'react-query';
import {useSaveStatus} from '../../../hooks/useSaveStatus';
import {Template} from '../../../models';
import {useTemplateForm} from '../../providers/TemplateFormProvider';
import {useDebounceCallback} from '../../../tools/useDebounceCallback';
import {Status} from '../../providers/SaveStatusProvider';
import {BadRequestError} from '../../../tools/apiFetch';

// Temporary, awaiting the implementation of the API endpoint
const useUpdateTemplateProperties = (templateUuid: string) => {
  return useMutation<
    void,
    BadRequestError<
      Array<{
        error: {
          property: string;
          message: string;
        };
      }>
    >,
    {labels: {[localeCode: string]: string}}
  >(() => new Promise<void>(resolve => setTimeout(resolve, 150)));
};

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
    handleStatusListChange(saveStatusId, Status.SAVING);
    await mutation.mutateAsync(
      {labels: {[locale.code]: value}},
      {
        onError: error => {
          const errors = error.data.map(({error}) => error.message);
          dispatch({
            type: 'save_template_label_translation_failed',
            payload: {localeCode: locale.code, errors},
          });
          handleStatusListChange(saveStatusId, Status.ERRORS);
        },
      }
    );
    await queryClient.invalidateQueries(['get-template', template.uuid]);
    dispatch({
      type: 'template_label_translation_saved',
      payload: {localeCode: locale.code, value},
    });
    handleStatusListChange(saveStatusId, Status.SAVED);
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
