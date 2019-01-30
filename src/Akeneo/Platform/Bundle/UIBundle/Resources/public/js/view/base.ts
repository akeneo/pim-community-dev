import * as _ from 'underscore';
import * as JQuery from 'jquery';
import * as Backbone from 'backbone';
import View from 'pimui/js/view/base-interface';
const mediator = require('oro/mediator');

/**
 * View base class
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseView extends Backbone.View<any> implements View {
  private parent: View | null = null;
  private extensions: {[code: string]: View};

  readonly preUpdateEventName: string = 'pim_enrich:form:entity:pre_update';
  readonly postUpdateEventName: string = 'pim_enrich:form:entity:post_update';

  public code: string = 'form';
  public configured: boolean;
  public zones: {[code: string]: any};
  public targetZone: string;
  public position: number;

  /**
   * {@inheritdoc}
   */
  constructor(config: any) {
    super(config);

    this.extensions = {};
    this.zones = {};
    this.targetZone = '';
    this.configured = false;
  }

  /**
   * Configure the extension and its child extensions
   *
   * @return {JQueryPromise<any>}
   */
  configure(): JQueryPromise<any> {
    if (null === this.parent) {
      this.model = new Backbone.Model();
    }

    const extensionPromises = Object.values(this.extensions).map((extension: View) => {
      return extension.configure();
    });

    return JQuery.when(...extensionPromises).then(() => {
      this.configured = true;
    });
  }

  /**
   * Add a child extension to this extension
   *
   * @param {string} code      Extension's code
   * @param {View}   extension Backbone module of the extension
   * @param {string} zone      Targeted zone
   * @param {number} position  The position of the extension
   */
  addExtension(code: string, extension: View, zone: string, position: number) {
    extension.setParent(this);

    extension.code = code;
    extension.targetZone = zone;
    extension.position = position;

    if (undefined === this.extensions || null === this.extensions) {
      throw 'this.extensions have to be defined. Please ensure you called parent initialize() method.';
    }

    this.extensions[code] = extension;
  }

  /**
   * Get a child extension (the first extension matching the given code or ends with the given code)
   *
   * @param  {string} code
   * @return {View}
   */
  getExtension(code: string): View {
    const extensionKey = _.findKey(this.extensions, (extension: View) => {
      const expectedPosition = extension.code.length - code.length;

      return expectedPosition >= 0 && expectedPosition === extension.code.indexOf(code, expectedPosition);
    });

    return this.extensions[extensionKey];
  }

  /**
   * Set the parent of this extension
   *
   * @param {View} parent
   */
  setParent(parent: View) {
    this.parent = parent;
  }

  /**
   * Get the parent of the extension
   *
   * @return {View | null}
   */
  getParent(): View | null {
    return this.parent;
  }

  /**
   * Get the root extension
   *
   * @return {View}
   */
  getRoot(): View {
    let rootView = <View>this;
    let parent = this.getParent();

    while (null !== parent) {
      rootView = parent;
      parent = parent.getParent();
    }

    return rootView;
  }

  /**
   * Set data in the root model
   *
   * @param {any}                data
   * @param {{silent?: boolean}} options If silent is set to true, don't fire events
   *                                       pim_enrich:form:entity:pre_update and pim_enrich:form:entity:post_update
   */
  setData(data: any, options: {silent?: boolean} = {}) {
    if (!options.silent) {
      this.getRoot().trigger(this.preUpdateEventName, data);
    }

    this.getRoot().model.set(data, options);

    if (!options.silent) {
      this.getRoot().trigger(this.postUpdateEventName, data);
    }
  }

  /**
   * Get the form raw data (vanilla javascript object)
   *
   * @return {any}
   */
  getFormData(): any {
    return this.getRoot().model.toJSON();
  }

  /**
   * Get the form data (backbone model)
   *
   * @return {Backbone.Model}
   */
  getFormModel(): Backbone.Model {
    return this.getRoot().model;
  }

  /**
   * Called before removing the form from the view
   */
  shutdown() {
    this.doShutdown();

    Object.values(this.extensions).forEach((extension: View) => extension.shutdown());
  }

  /**
   * The actual shutdown method called on all extensions
   */
  doShutdown() {
    this.stopListening();
    this.undelegateEvents();
    this.$el.removeData().off();
    this.remove();

    Backbone.View.prototype.remove.call(this);
  }

  /**
   * {@inheritdoc}
   */
  render(): View {
    if (!this.configured) {
      return this;
    }

    return this.renderExtensions();
  }

  /**
   * Render the child extensions
   *
   * @return {View}
   */
  renderExtensions(): View {
    // If the view is no longer attached to the DOM, don't render the extensions
    if (undefined === this.el) {
      return this;
    }

    this.initializeDropZones();

    Object.values(this.extensions).forEach((extension: View) => {
      this.renderExtension(extension);
    });

    return this;
  }

  /**
   * Render a single extension
   *
   * @param {View} extension
   */
  renderExtension(extension: View) {
    var zone = this.getZone(extension.targetZone);

    if (null === zone) {
      throw new Error(
        `Can not render extension "${extension.code}" in "${this.code}": zone "${extension.targetZone}" does not exist`
      );
    }

    zone.appendChild(extension.el);

    extension.render();
  }

  /**
   * Initialize dropzone cache
   */
  initializeDropZones() {
    this.zones = this.$('[data-drop-zone]')
      .toArray()
      .reduce((zones: {[code: string]: HTMLElement}, zone: HTMLElement) => {
        return {...zones, [<string>zone.dataset.dropZone]: zone};
      }, {});

    this.zones['self'] = this.el;
  }

  /**
   * Get the drop zone for the given code
   *
   * @param {string} code
   *
   * @return {HTMLElement | null}
   */
  getZone(code: string): HTMLElement | null {
    if (!(code in this.zones)) {
      this.zones[code] = this.el.querySelector(`[data-drop-zone="${code}"]`);
    }

    if (!this.zones[code]) {
      return null;
    }

    return this.zones[code];
  }

  /**
   * Trigger event on each child extensions and their childs
   */
  triggerExtensions() {
    const options = Object.values(arguments);

    Object.values(this.extensions).forEach(extension => {
      extension.trigger.apply(extension, options);
      extension.triggerExtensions.apply(extension, options);
    });
  }

  /**
   * Listen on child extensions and their childs events
   *
   * @param {string}   code
   * @param {Function} callback
   */
  onExtensions(code: string, callback: any) {
    Object.values(this.extensions).forEach((extension: View) => {
      this.listenTo(extension, code, callback);
    });
  }

  /**
   * Get the root form code
   *
   * @return {string}
   */
  getFormCode(): string {
    return this.getRoot().code;
  }

  /**
   * Listen to given mediator events to trigger them locally (in the local root).
   * This way, extensions attached to this form don't have to listen "globally" on the mediator.
   *
   * @param {{[mediatorEvent: string]: string}} mediator events to forward:
   *                {'mediator:event:name': 'this:event:name', ...}
   */
  forwardMediatorEvents(events: {[mediatorEvent: string]: string}) {
    Object.keys(events).forEach((localEvent: string) => {
      const mediatorEvent = events[localEvent];

      this.listenTo(mediator, mediatorEvent, (...args: any[]) => {
        this.trigger(localEvent, ...args);
      });
    });
  }
}

export = BaseView;
