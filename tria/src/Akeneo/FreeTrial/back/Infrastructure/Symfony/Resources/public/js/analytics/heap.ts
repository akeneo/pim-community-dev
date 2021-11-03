import {getHeapAgent} from "./heap-agent";

const UserContext = require('pim/user-context');

const Heap = {
  init: async () => {
    const heapAgent = await getHeapAgent();

    if(null === heapAgent) {
      throw new Error('Heap is not initialized');
    }

    heapAgent.identify(UserContext.get('username'));
    heapAgent.addUserProperties({
      email: UserContext.get('email'),
      firstName: UserContext.get('first_name'),
      lastName: UserContext.get('last_name'),
    });
  },
};

export = Heap;
