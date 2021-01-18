const UserContext = require('pim/user-context');
const mediator = require('oro/mediator');

type HeapAgent = {
  identify: (id: string) => void;
  addUserProperties: (options: object) => void;
  track: (event: string, options?: object) => void;
};

type PendoAgent = {
  initialize: (options: object) => void;
  identify: (identity: object) => void;
};

type AppcuesAgent = {
  identify: (uid: string | number, options: object) => void;
  page: () => void;
};

class PimOnBoarding {
  private heap: HeapAgent;
  private pendo: PendoAgent;
  private appcues: AppcuesAgent;

  public initialize() {
    mediator.on('route_complete', async (name: string) => {
      this.registerPage(name);
    });
  }

  public async registerPage(page: string) {
    setTimeout(() => {
      // @ts-ignore
      this.heap = window.heap;
      // @ts-ignore
      this.pendo = window.pendo;
      // @ts-ignore
      this.appcues = window.appcues;

      this.heap.track('Page', {page});
      this.appcues.page();
    }, 500);
  }

  public async registerUser() {
    setTimeout(() => {
      // @ts-ignore
      this.heap = window.heap;
      // @ts-ignore
      this.pendo = window.pendo;
      // @ts-ignore
      this.appcues = window.appcues;

      this.registerUserWithHeap();
      this.registerUserWithPendo();
      this.registerUserWithAppcues();
    }, 500);
  }

  private registerUserWithHeap() {
    const userId = UserContext.get('meta').id;

    this.heap.identify(UserContext.get('username'));
    this.heap.addUserProperties({
      email: UserContext.get('email'),
      language: UserContext.get('uiLocale'),
      userId: userId,
      firstName: UserContext.get('first_name'),
      lastName: UserContext.get('last_name'),
      role: UserContext.get('roles').join(', '),
    });
  }

  private registerUserWithPendo() {
    this.pendo.identify({
      visitor: {
        id: UserContext.get('username'), // Required if user is logged in
        email: UserContext.get('email'),
        first_name: UserContext.get('first_name'),
        last_name: UserContext.get('last_name'),
        role: UserContext.get('roles').join(', '),
      },
    });
  }

  private registerUserWithAppcues() {
    const userId = UserContext.get('meta').id;

    this.appcues.identify(UserContext.get('username'), {
      // recommended (but optional) properties
      plan_tier: process.env.EDITION, //'Akeneo Trial Edition', // Current user’s plan tier
      role: UserContext.get('roles').join(', '), // Current user’s role or permissions
      account_id: userId, // Current user's account ID
      first_name: UserContext.get('first_name'), // Current user's first name
      last_name: UserContext.get('last_name'), // Current user's first name

      // additional suggestions
      company_name: 'Akeneo', // Current user’s company
      email: UserContext.get('email'), // Current user's email
      language: UserContext.get('uiLocale'), // for multi-language applications
    });
  }
}

const pimOnBoarding = new PimOnBoarding();
pimOnBoarding.initialize();

export = pimOnBoarding;
