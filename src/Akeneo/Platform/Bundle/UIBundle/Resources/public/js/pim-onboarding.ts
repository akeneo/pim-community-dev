const UserContext = require('pim/user-context');
const mediator = require('oro/mediator');

type HeapAgent = {
  identify: (id: string) => void;
  addUserProperties: (options: object) => void;
  track: (event: string, options?: object) => void;
};

type AppcuesAgent = {
  identify: (uid: string | number, options: object) => void;
  page: () => void;
};

class PimOnBoarding {
  private heap: HeapAgent;
  private appcues: AppcuesAgent;

  public constructor() {
    // @ts-ignore
    this.heap = window.heap;
    // @ts-ignore
    this.appcues = window.Appcues;
  }

  public initialize() {
    mediator.on('route_complete', async (name: string) => {
      this.registerPage(name);
    });
  }

  public async registerPage(page: string) {
    setTimeout(() => {
      this.heap.track('Page', {page});
      //pendo is already initialized after the pendo library is loaded
      this.appcues.page();
    }, 100);
  }

  public async registerUser() {
    setTimeout(() => {
      this.registerUserWithHeap();
      this.registerUserWithAppcues();
    }, 100);
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
