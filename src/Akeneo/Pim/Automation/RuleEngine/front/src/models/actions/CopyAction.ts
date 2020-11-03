import {CopyActionLine} from '../../pages/EditRules/components/actions/CopyActionLine';
import {ActionModuleGuesser} from './ActionModuleGuesser';

export type CopyAction = {
  type: 'copy';
  from_field: string | null;
  from_locale?: string | null;
  from_scope?: string | null;
  to_field: string | null;
  to_locale?: string | null;
  to_scope?: string | null;
};

export const getCopyActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'copy') {
    return Promise.resolve(null);
  }

  return Promise.resolve(CopyActionLine);
};

export const createCopyAction = (): CopyAction => ({
  type: 'copy',
  from_field: null,
  to_field: null,
});
