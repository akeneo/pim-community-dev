import {Security} from '../DependenciesProvider.type';
import {getJson} from './http';

const SECURITY_CONTEXT_URL = '/rest/security/';

type Acls = {[acl: string]: boolean};

let acls: Acls = {};

/**
 * ACLs granted to the current user, historically `pim/security-context`.
 */
const securityContext: Security & {
  initialize: () => Promise<Acls>;
  get: (acl: string) => boolean | undefined;
} = {
  initialize: async () => {
    acls = await getJson<Acls>(SECURITY_CONTEXT_URL);

    return acls;
  },
  get: (acl: string) => acls[acl],
  isGranted: (acl: string) => acls[acl] === true,
};

export {securityContext};
