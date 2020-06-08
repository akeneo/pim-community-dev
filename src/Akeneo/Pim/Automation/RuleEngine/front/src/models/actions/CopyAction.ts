import { CopyActionLine } from '../../pages/EditRules/components/actions/CopyActionLine';
import { ActionModuleGuesser } from '../Action';

export type CopyAction = {
  type: 'copy';
  from_field: string;
  from_locale: string | null;
  from_scope: string | null;
  to_field: string;
  to_locale: string | null;
  to_scope: string | null;
};

export const getCopyActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'copy') {
    return Promise.resolve(null);
  }

  return Promise.resolve(CopyActionLine);
};
