import ReactDOM from 'react-dom';
import React, {useCallback} from 'react';
import {AnimateMessageBar, MessageBar, pimTheme, uuid} from 'akeneo-design-system';
import styled, {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

const Container = styled.div`
  display: flex;
  flex-direction: column-reverse;
`;

type FlashMessage = {
  identifier: string;
  type: string;
  message: string;
  options: {messageTitle: string};
};

const Notifications = ({
  notifications,
  onNotificationClosed,
}: {
  notifications: FlashMessage[];
  onNotificationClosed: (notifications: FlashMessage) => void;
}) => {
  const handleClose = useCallback(
    (notification: FlashMessage) => () => {
      onNotificationClosed(notification);
    },
    []
  );

  return (
    <Container>
      {notifications.map(notification => (
        <AnimateMessageBar key={notification.identifier}>
          <MessageBar title={notification.message} onClose={handleClose(notification)}></MessageBar>
        </AnimateMessageBar>
      ))}
    </Container>
  );
};

let notifications: FlashMessage[] = [];

const render = () => {
  ReactDOM.render(
    <ThemeProvider theme={pimTheme}>
      <DependenciesProvider>
        <Notifications
          notifications={notifications}
          onNotificationClosed={(notification: FlashMessage) => {
            notifications = notifications.filter(currentNotif => currentNotif.identifier !== notification.identifier);
            render();
          }}
        />
      </DependenciesProvider>
    </ThemeProvider>,
    document.getElementById('flash-messages')
  );
};

/**
 * Shows notification message
 *
 * @param {(string|boolean)} type 'error'|'success'|'warning'|false
 * @param {string} message text of message
 * @param {Object} options
 *
 * @param {(string|jQuery)} options.container selector of jQuery with container element
 * @param {(number|boolean)} options.delay time in ms to auto close message
 *      or false - means to not close automatically
 * @param {Function} options.template template function
 * @param {boolean} options.flash flag to turn on default delay close call, it's 5s
 */
const notify = (type: string, message: string, options: {messageTitle: string}) => {
  notifications.push({identifier: uuid(), type, message, options});
  render();
};

module.exports = {notify};
// queue: [],
// defaults: {
//   container: '#flash-messages .flash-messages-holder',
//   delay: false,
//   template: _.template(flashMessageTemplate),
//   flash: true,
//   messageTitle: null,
// },

// /**
//  * Shows notification message
//  *
//  * @param {(string|boolean)} type 'error'|'success'|'warning'|false
//  * @param {string} message text of message
//  * @param {Object} options
//  *
//  * @param {(string|jQuery)} options.container selector of jQuery with container element
//  * @param {(number|boolean)} options.delay time in ms to auto close message
//  *      or false - means to not close automatically
//  * @param {Function} options.template template function
//  * @param {boolean} options.flash flag to turn on default delay close call, it's 5s
//  */
// notify: function (type, message, options) {
//   this.showMessage(type, message, options);
// },

// enqueueMessage: function () {
//   this.queue.push(arguments);
// },

// showQueuedMessages: function () {
//   while (this.queue.length) {
//     var args = this.queue.shift();
//     this.showMessage.apply(this, args);
//   }
// },

// showMessage: function (type, message, options) {
//   var opt = _.extend({}, this.defaults, options || {});
//   var delay = opt.delay || (opt.flash && 5000);
//   var $el = $(
//     opt.template({
//       type: type,
//       message: message,
//       messageTitle: opt.messageTitle,
//       delay: delay,
//       icon: this.getIcon(type),
//       closeIcon: this.getCloseIcon(type),
//     })
//   ).appendTo(opt.container);

//   // Used to force the browser to visually render the element's styles to be able to use CSS transitions
//   $el.offset();
//   $el.addClass('AknFlash--visible');

//   if (delay) {
//     var timeLeft = delay;
//     var interval = setInterval(function () {
//       $el.find('.flash-timer:first').html(Math.max(Math.floor(timeLeft / 1000), 0));
//       timeLeft -= 500;

//       if (timeLeft <= 0) {
//         $el.removeClass('AknFlash--visible');
//       }

//       if (timeLeft <= -500) {
//         $el.addClass('AknFlash--crushed');
//       }

//       if (timeLeft <= -1500) {
//         $el.remove();
//         clearInterval(interval);
//       }
//     }, 500);
//   }
// },

// getIcon: function (type) {
//   return _.result(
//     {
//       info: 'icon-infos.svg',
//       success: 'icon-check.svg',
//       error: 'icon-warning-redlight.svg',
//       warning: 'icon-warning-orangelight.svg',
//     },
//     type,
//     'icon-infos.svg'
//   );
// },

// getCloseIcon: function (type) {
//   return _.result(
//     {
//       info: 'icon-delete-bluedark.svg',
//       success: 'icon-delete-greendark.svg',
//       error: 'icon-delete-reddark.svg',
//       warning: 'icon-delete-orangedark.svg',
//     },
//     type,
//     'icon-delete-bluedark.svg'
//   );
// },
// };
