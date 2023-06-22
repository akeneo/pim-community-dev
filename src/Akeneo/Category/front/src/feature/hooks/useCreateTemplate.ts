import {useMutation} from 'react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {apiFetch, BadRequestError} from '../tools/apiFetch';

type Form = {
  categoryTreeId: string;
  code: string;
  locale: string;
  label: string | null;
}

export const useCreateTemplate = () => {}
