import $ from 'jquery';
import {mediator} from '@akeneo-pim-community/shared';

const mockControllers: {[route: string]: any} = {};
const mockSecurity = {isGranted: jest.fn((_acl: string) => true)};
const mockFeatureFlags = {isEnabled: jest.fn((_feature: string) => false)};
const mockErrorRender = jest.fn();
const mockErrorView = jest.fn();
const mockLoadingMask = {show: jest.fn(), hide: jest.fn()};

jest.mock('routing', () => {
  const routes: {[name: string]: any} = {
    oro_default: {tokens: [['text', '/']], defaults: {}, requirements: []},
    pim_dashboard_index: {tokens: [['text', '/dashboard']], defaults: {}, requirements: []},
    pim_enrich_product_index: {tokens: [['text', '/enrich/product/']], defaults: {}, requirements: []},
    pim_enrich_locale_index: {tokens: [['text', '/configuration/locale/']], defaults: {}, requirements: []},
    pim_user_security_login: {tokens: [['text', '/user/login']], defaults: {}, requirements: []},
  };

  return {
    getRoutes: () => routes,
    getBaseUrl: () => '',
    generate: (name: string) => routes[name].tokens.map((token: string[]) => token[1]).join(''),
  };
});
jest.mock('oro/translator', () => (key: string) => key, {virtual: true});
jest.mock('oro/mediator', () => require('@akeneo-pim-community/shared').mediator, {virtual: true});
jest.mock('pim/route-matcher', () => require('../../../Resources/public/js/route-matcher'), {virtual: true});
jest.mock('pim/security-context', () => mockSecurity, {virtual: true});
jest.mock('pim/feature-flags', () => mockFeatureFlags, {virtual: true});
jest.mock(
  'pim/controller-registry',
  () => ({
    get: (name: string) => $.Deferred().resolve(mockControllers[name] || mockControllers.default),
  }),
  {virtual: true}
);
jest.mock(
  'oro/loading-mask',
  () =>
    function () {
      return {
        render: () => ({$el: $('<div class="loading-mask"></div>')}),
        show: mockLoadingMask.show,
        hide: mockLoadingMask.hide,
      };
    },
  {virtual: true}
);
jest.mock(
  'pim/error',
  () =>
    function (message: string, code: string) {
      mockErrorView(message, code);

      return {setElement: () => ({render: mockErrorRender})};
    },
  {virtual: true}
);

class FakeController {
  options: any;
  el: any;
  canLeaveResult = true;
  static instances: FakeController[] = [];
  static renderRoute = jest.fn((): any => $.Deferred().resolve());

  constructor(options: any) {
    this.options = options;
    this.el = options.el;
    FakeController.instances.push(this);
  }

  setActive = jest.fn();
  remove = jest.fn();
  canLeave = () => this.canLeaveResult;
  renderRoute = (route: any, path: string) => FakeController.renderRoute(route, path);
}

const router = jest.requireActual('pim/router');

const flushPromises = () => new Promise(resolve => setTimeout(resolve, 20));

beforeAll(() => {
  document.body.innerHTML = '<div class="hash-loading-mask"></div><div id="container"></div>';
  window.location.hash = '#/dashboard';
  mockControllers.pim_dashboard_index = {class: FakeController};
  mockControllers.default = {class: FakeController};
  router.indexRoute = 'pim_dashboard_index';
  router.start();
});

beforeEach(() => {
  FakeController.instances = [];
  FakeController.renderRoute.mockClear();
  FakeController.renderRoute.mockImplementation(() => $.Deferred().resolve());
  mockErrorView.mockClear();
  mockSecurity.isGranted.mockImplementation(() => true);
  mockFeatureFlags.isEnabled.mockImplementation(() => false);
});

test('it renders the current route when started, only once', () => {
  expect(router.isStarted()).toBe(true);
  expect(document.querySelector('.hash-loading-mask .loading-mask')).not.toBeNull();
  expect(router.getFragment()).toBe('dashboard');

  router.start();
  expect(router.isStarted()).toBe(true);
});

test('it renders the controller of the route in the container and triggers the route events', () => {
  const events: string[] = [];
  const onStart = () => events.push('route_start');
  const onComplete = () => events.push('route_complete');
  mediator.on('route_start', onStart);
  mediator.on('route_complete', onComplete);
  const routeListener = jest.fn();
  router.on('route', routeListener);

  router.redirect('/enrich/product/', {trigger: true});

  expect(FakeController.instances).toHaveLength(1);
  const controller = FakeController.instances[0];
  expect(controller.setActive).toHaveBeenCalledWith(true);
  expect(controller.el.get(0).parentElement.id).toBe('container');
  expect(FakeController.renderRoute).toHaveBeenCalledWith(
    expect.objectContaining({name: 'pim_enrich_product_index'}),
    '/enrich/product/'
  );
  expect(events).toEqual(['route_start', 'route_complete']);
  expect(routeListener).toHaveBeenCalledWith('defaultRoute', ['enrich/product/']);
  expect(window.location.hash).toBe('#/enrich/product/');
  expect(mockLoadingMask.show).toHaveBeenCalled();
  expect(mockLoadingMask.hide).toHaveBeenCalled();

  mediator.off('route_start', onStart);
  mediator.off('route_complete', onComplete);
  router.off('route', routeListener);
});

test('it removes the previous controller and reloads the current page', () => {
  router.redirectToRoute('pim_enrich_product_index', {}, {trigger: true});
  const previous = FakeController.instances[FakeController.instances.length - 1];

  router.reloadPage();

  expect(previous.remove).toHaveBeenCalled();
  expect(FakeController.instances[FakeController.instances.length - 1]).not.toBe(previous);
});

test('its methods can be called detached from the router', () => {
  const {redirect, redirectToRoute, generate, reloadPage} = router;

  redirect('/configuration/locale/', {trigger: true});
  expect(window.location.hash).toBe('#/configuration/locale/');

  redirectToRoute('pim_enrich_product_index', {}, {trigger: true});
  expect(window.location.hash).toBe('#/enrich/product/');
  const previous = FakeController.instances[FakeController.instances.length - 1];

  reloadPage();
  expect(FakeController.instances[FakeController.instances.length - 1]).not.toBe(previous);
  expect(generate('pim_dashboard_index')).toBe('/dashboard');
});

test('it completes the route synchronously for controllers returning resolved jQuery promises', () => {
  const listener = jest.fn();
  mediator.on('route_complete', listener);
  router.redirect('/enrich/product/', {trigger: true});

  expect(listener).toHaveBeenCalled();
  mediator.off('route_complete', listener);
});

test('it supports controllers returning native promises', async () => {
  const listener = jest.fn();
  mediator.on('route_complete', listener);
  FakeController.renderRoute.mockImplementation(() => Promise.resolve());

  router.redirect('/enrich/product/', {trigger: true});
  await flushPromises();

  expect(listener).toHaveBeenCalled();
  mediator.off('route_complete', listener);
});

test('it does not leave a controller refusing it', () => {
  router.redirect('/enrich/product/', {trigger: true});
  FakeController.instances[0].canLeaveResult = false;

  expect(router.redirect('/configuration/locale/', {trigger: true})).toBe(false);
  expect(FakeController.instances).toHaveLength(1);
  FakeController.instances[0].canLeaveResult = true;
});

test('it renders the index route for the default route and the empty fragment', () => {
  router.navigate('', {trigger: true, force: true});
  expect(FakeController.renderRoute).toHaveBeenLastCalledWith(
    expect.objectContaining({name: 'pim_dashboard_index'}),
    expect.stringContaining('/dashboard')
  );

  FakeController.renderRoute.mockClear();
  router.defaultRoute('/');
  expect(FakeController.renderRoute).toHaveBeenLastCalledWith(
    expect.objectContaining({name: 'pim_dashboard_index'}),
    expect.stringContaining('/dashboard')
  );
});

test('it ignores the part of the path after a pipe', () => {
  router.defaultRoute('enrich/product/|grid');

  expect(FakeController.renderRoute).toHaveBeenLastCalledWith(
    expect.objectContaining({name: 'pim_enrich_product_index'}),
    '/enrich/product/'
  );
});

test('it displays an error page for unknown routes, disabled features and forbidden routes', () => {
  router.defaultRoute('/zz-unknown-zz/route/that/does/not/exist');
  expect(mockErrorView).toHaveBeenLastCalledWith('Page not found', 404);

  mockControllers.pim_enrich_locale_index = {class: FakeController, feature: 'a_feature'};
  router.defaultRoute('/configuration/locale/');
  expect(mockErrorView).toHaveBeenLastCalledWith('Page not found', 404);

  mockControllers.pim_enrich_locale_index = {class: FakeController, aclResourceId: 'pim_enrich_locale_index'};
  mockSecurity.isGranted.mockImplementation(() => false);
  router.defaultRoute('/configuration/locale/');
  expect(mockErrorView).toHaveBeenLastCalledWith('error.forbidden', '403');

  delete mockControllers.pim_enrich_locale_index;
});

test('it handles the errors of the controllers', () => {
  FakeController.renderRoute.mockImplementation(() => $.Deferred().reject({status: 500, statusText: 'Server error'}));
  router.defaultRoute('/enrich/product/');
  expect(mockErrorView).toHaveBeenLastCalledWith('Server error', 500);

  FakeController.renderRoute.mockImplementation(() => $.Deferred().reject({status: 403}));
  router.defaultRoute('/enrich/product/');
  expect(mockErrorView).toHaveBeenLastCalledWith('error.forbidden', 403);

  mockErrorView.mockClear();
  FakeController.renderRoute.mockImplementation(() => $.Deferred().reject({status: 200}));
  router.defaultRoute('/enrich/product/');
  expect(mockErrorView).toHaveBeenCalledTimes(1);
  expect(mockErrorView).toHaveBeenLastCalledWith('An error occurred', undefined);
});

test('it redirects to the login page when the session expired', () => {
  const generate = jest.spyOn(router, 'generate').mockImplementation(() => '#login');
  FakeController.renderRoute.mockImplementation(() => $.Deferred().reject({status: 401}));
  router.defaultRoute('/enrich/product/');

  expect(generate).toHaveBeenCalledWith('pim_user_security_login');
  generate.mockRestore();
});

test('it forces the reload of the current page', () => {
  router.redirect('/enrich/product/', {trigger: true});
  const count = FakeController.instances.length;

  expect(router.navigate('enrich/product/', true)).toBe(false);
  router.forgetFragment();
  expect(router.navigate('enrich/product/', true)).toBe(true);
  expect(FakeController.instances).toHaveLength(count + 1);
});

test('it navigates without loading the page synchronously', () => {
  const count = FakeController.instances.length;
  router.navigate('#/configuration/locale/');

  expect(FakeController.instances).toHaveLength(count);
  expect(window.location.hash).toBe('#/configuration/locale/');
});

test('it processes the route links', () => {
  document.body.innerHTML +=
    '<a id="valid" route="pim_enrich_product_index" route-params="{}"></a>' +
    '<a id="invalid" route="pim_enrich_product_index" route-params="{invalid"></a>' +
    '<a id="none" route="pim_enrich_product_index"></a>';
  mediator.trigger('route_complete');

  expect(document.getElementById('valid')!.getAttribute('href')).toBe('#/enrich/product/');
  expect(document.getElementById('invalid')!.getAttribute('href')).toBe('#/enrich/product/');
  expect(document.getElementById('none')!.getAttribute('href')).toBe('#/enrich/product/');
});

test('it hides and shows the loading mask', () => {
  router.showLoadingMask();
  router.hideLoadingMask();

  expect(mockLoadingMask.show).toHaveBeenCalled();
  expect(mockLoadingMask.hide).toHaveBeenCalled();
});
