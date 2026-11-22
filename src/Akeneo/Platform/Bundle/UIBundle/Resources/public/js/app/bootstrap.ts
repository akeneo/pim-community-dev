import {initTranslator, userContext} from '@akeneo-pim-community/shared';

/* eslint-disable @typescript-eslint/no-var-requires */
const FormRegistry = require('pim/form-registry');
const FormBuilder = require('pim/form-builder');
const FetcherRegistry = require('pim/fetcher-registry');
const DateContext = require('pim/date-context');
const pageTitle = require('pim/page-title');
const initLegacyPageBehaviors = require('pim/init');
const initLayout = require('oro/init-layout');
const initSignin = require('pimuser/js/init-signin');
const installBackboneHistoryCompat = require('pim/legacy/backbone-history-compat');

/**
 * Code of the form extension whose children are rendered in the application shell zones (menu, side panels...).
 * Bundles keep registering their shell extensions with `parent: pim-app` and a `targetZone`.
 */
const APPLICATION_EXTENSION = 'pim-app';

type LegacyView = {
  el: HTMLElement;
  code: string;
  render: () => unknown;
  remove: () => unknown;
};

type ExtensionMeta = {
  code: string;
  module: string | null;
  targetZone: string;
  position: number;
};

type ApplicationZones = {[zone: string]: LegacyView[]};

const toPromise = <T>(promise: PromiseLike<T>): Promise<T> => Promise.resolve(promise);

/**
 * Builds (and configures) the extensions registered in the application shell, grouped by zone.
 */
const buildApplicationZones = async (): Promise<ApplicationZones> => {
  const extensions: ExtensionMeta[] = await toPromise(FormRegistry.getFormExtensions({code: APPLICATION_EXTENSION}));
  const buildableExtensions = extensions.filter(extension => null !== extension.module);
  const views: LegacyView[] = await Promise.all(
    buildableExtensions.map(extension => toPromise<LegacyView>(FormBuilder.build(extension.code)))
  );

  return buildableExtensions.reduce<ApplicationZones>((zones, extension, index) => {
    zones[extension.targetZone] = [...(zones[extension.targetZone] || []), views[index]];

    return zones;
  }, {});
};

/**
 * Loads everything the application needs before rendering the first page.
 * It reproduces the boot sequence of the former Backbone application view (`pim/app`).
 */
const bootstrapApplication = async (): Promise<ApplicationZones> => {
  // Loads the form extensions configuration, the ACLs and the feature flags
  await toPromise(FormRegistry.getFormMeta(APPLICATION_EXTENSION));

  installBackboneHistoryCompat();
  initLayout();
  initSignin();

  await Promise.all([
    toPromise(FetcherRegistry.initialize()),
    toPromise(DateContext.initialize()),
    userContext.initialize(),
  ]);
  await initTranslator.fetch();

  initLegacyPageBehaviors();
  pageTitle.set('Akeneo PIM');

  return buildApplicationZones();
};

export {bootstrapApplication, APPLICATION_EXTENSION};
export type {ApplicationZones, LegacyView};
