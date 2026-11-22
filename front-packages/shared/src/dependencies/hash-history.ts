/**
 * Hash based navigation reproducing the Backbone.history (0.9.10, hash change mode) algorithm without Backbone.
 *
 * The algorithm is kept on purpose, including its quirks, as the whole legacy application relies on it:
 * - a fragment is normalized by stripping ONE leading "#" or "/" (and trailing spaces),
 * - `navigate()` is a no-op when the normalized fragment equals the current one (unless `force` is set),
 * - `navigate(fragment, {trigger: true})` updates the hash and loads the fragment synchronously,
 * - `navigate(fragment)` only updates the hash, the "hashchange" event then loads it if it differs,
 * - as loading stores the fragment without its leading "/", redirecting to "#/current/url" with `trigger` reloads it.
 */
type NavigateOptions = {trigger?: boolean; replace?: boolean; force?: boolean};
type FragmentListener = (fragment: string) => void;

const fragmentStripper = /^[#\/]|\s+$/g;

const normalizeFragment = (fragment: string): string => fragment.replace(fragmentStripper, '');

const createHashHistory = (target: Window = window) => {
  let listener: FragmentListener | null = null;
  let currentFragment: string | null = null;

  const getHash = (): string => {
    const match = target.location.href.match(/#(.*)$/);

    return match ? match[1] : '';
  };

  const loadUrl = (fragmentOverride?: string) => {
    currentFragment = normalizeFragment(undefined === fragmentOverride ? getHash() : fragmentOverride);
    (listener as FragmentListener)(currentFragment);
  };

  const checkUrl = () => {
    if (normalizeFragment(getHash()) === currentFragment) {
      return;
    }

    loadUrl();
  };

  const updateHash = (fragment: string, replace: boolean) => {
    if (replace) {
      const href = target.location.href.replace(/(javascript:|#).*$/, '');
      target.location.replace(`${href}#${fragment}`);
    } else {
      target.location.hash = `#${fragment}`;
    }
  };

  return {
    isStarted: () => null !== listener,

    start(onChange: FragmentListener, options: {silent?: boolean} = {}) {
      if (null !== listener) {
        throw new Error('The hash history has already been started');
      }
      listener = onChange;
      target.addEventListener('hashchange', checkUrl);
      currentFragment = normalizeFragment(getHash());

      if (!options.silent) {
        loadUrl();
      }
    },

    stop() {
      target.removeEventListener('hashchange', checkUrl);
      listener = null;
      currentFragment = null;
    },

    getFragment: () => currentFragment,

    /**
     * Forgets the current fragment, so that the next navigation (even to the same URL) loads it.
     */
    forget() {
      currentFragment = null;
    },

    navigate(fragment: string, options: NavigateOptions = {}): boolean {
      if (null === listener) {
        return false;
      }

      const normalized = normalizeFragment(fragment || '');
      if (normalized === currentFragment && !options.force) {
        return false;
      }

      currentFragment = normalized;
      updateHash(normalized, Boolean(options.replace));
      if (options.trigger) {
        loadUrl(normalized);
      }

      return true;
    },
  };
};

type HashHistory = ReturnType<typeof createHashHistory>;

export {createHashHistory, normalizeFragment};
export type {HashHistory, NavigateOptions};
