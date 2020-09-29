import {mountReactElementRef, unmountReactElementRef} from './reactElementHelper';
import BaseView = require('pimui/js/view/base');

abstract class ReactView extends BaseView {
  /**
   * Base React element to mount.
   */
  abstract reactElementToMount(): JSX.Element;

  render(): BaseView {
    this.$el.append(mountReactElementRef(this.reactElementToMount(), this.$el.get(0)));

    return BaseView.prototype.render.apply(this, arguments);
  }

  remove(): BaseView {
    unmountReactElementRef(this.$el.get(0));

    return BaseView.prototype.remove.apply(this, arguments);
  }
}

export {ReactView};
