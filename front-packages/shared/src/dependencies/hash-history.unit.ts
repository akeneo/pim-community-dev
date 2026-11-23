import {createHashHistory, normalizeFragment} from './hash-history';

const waitForHashChange = () => new Promise(resolve => setTimeout(resolve, 0));

let history: ReturnType<typeof createHashHistory>;

beforeEach(() => {
  window.location.hash = '';
  history = createHashHistory(window);
});

afterEach(() => history.stop());

test('it normalizes fragments like Backbone', () => {
  expect(normalizeFragment('#/enrich/product')).toBe('/enrich/product');
  expect(normalizeFragment('/enrich/product ')).toBe('enrich/product');
  expect(normalizeFragment('enrich/product')).toBe('enrich/product');
});

test('it loads the current fragment on start', () => {
  window.location.hash = '#/dashboard';
  const listener = jest.fn();
  history.start(listener);

  expect(history.isStarted()).toBe(true);
  expect(listener).toHaveBeenCalledWith('dashboard');
  expect(history.getFragment()).toBe('dashboard');
});

test('it can start silently and only once', () => {
  const listener = jest.fn();
  history.start(listener, {silent: true});

  expect(listener).not.toHaveBeenCalled();
  expect(() => history.start(listener)).toThrow('The hash history has already been started');
});

test('it does not navigate before being started', () => {
  expect(history.isStarted()).toBe(false);
  expect(history.navigate('#/dashboard', {trigger: true})).toBe(false);
});

test('it navigates and loads synchronously when triggered', () => {
  const listener = jest.fn();
  history.start(listener, {silent: true});

  expect(history.navigate('#/enrich/product', {trigger: true})).toBe(true);
  expect(window.location.hash).toBe('#/enrich/product');
  expect(listener).toHaveBeenCalledWith('enrich/product');
  expect(history.getFragment()).toBe('enrich/product');
});

test('it reloads the current page when redirecting to it with a leading slash, like Backbone', () => {
  const listener = jest.fn();
  history.start(listener, {silent: true});
  history.navigate('#/enrich/product', {trigger: true});
  history.navigate('#/enrich/product', {trigger: true});

  expect(listener).toHaveBeenCalledTimes(2);
});

test('it does nothing when navigating to the current normalized fragment', () => {
  const listener = jest.fn();
  history.start(listener, {silent: true});
  history.navigate('enrich/product', {trigger: true});

  expect(history.navigate('enrich/product', {trigger: true})).toBe(false);
  expect(history.navigate('', {trigger: true})).toBe(true);
  expect(listener).toHaveBeenCalledTimes(2);
});

test('it forces the navigation to the current fragment', () => {
  const listener = jest.fn();
  history.start(listener, {silent: true});
  history.navigate('enrich/product', {trigger: true});
  history.navigate('enrich/product', {trigger: true, force: true});

  expect(listener).toHaveBeenCalledTimes(2);
});

test('it loads the same fragment again after forgetting it', () => {
  const listener = jest.fn();
  history.start(listener, {silent: true});
  history.navigate('enrich/product', {trigger: true});
  history.forget();
  expect(history.getFragment()).toBeNull();
  history.navigate('enrich/product', {trigger: true});

  expect(listener).toHaveBeenCalledTimes(2);
});

test('it loads the fragment on hash change when navigating without trigger', async () => {
  const listener = jest.fn();
  history.start(listener, {silent: true});
  history.navigate('#/enrich/product');
  expect(listener).not.toHaveBeenCalled();

  await waitForHashChange();
  expect(listener).toHaveBeenCalledWith('enrich/product');
});

test('it loads the fragment when the user changes the hash', async () => {
  const listener = jest.fn();
  history.start(listener, {silent: true});
  window.location.hash = '#/settings';
  await waitForHashChange();

  expect(listener).toHaveBeenCalledWith('settings');

  window.dispatchEvent(new HashChangeEvent('hashchange'));
  expect(listener).toHaveBeenCalledTimes(1);
});

test('it replaces the current history entry', () => {
  const listener = jest.fn();
  history.start(listener, {silent: true});
  history.navigate('#/settings', {trigger: true, replace: true});

  expect(window.location.hash).toBe('#/settings');
  expect(listener).toHaveBeenCalledWith('settings');
});

test('it uses the global window by default', () => {
  expect(createHashHistory().isStarted()).toBe(false);
});
