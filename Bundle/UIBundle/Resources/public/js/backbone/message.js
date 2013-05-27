var Oro = Oro || {};

Oro.Messages = Oro.Messages || {};

Oro.Messages.topMessageContainer = '#flash-messages .flash-messages-frame';

Oro.Messages.topMessageHolder = '.flash-messages-holder';

Oro.Messages.messageContentContainer = '.alert-empty';

$(function() {
    Oro.Messages.showMessage = function(type, message) {
        var newMessage = $(Oro.Messages.topMessageContainer + ' ' + Oro.Messages.messageContentContainer).clone();
        newMessage.find('.message').html(message);
        newMessage.removeClass(Oro.Messages.messageContentContainer.replace('.', ''));
        newMessage.addClass('alert-' + type);
        $(Oro.Messages.topMessageContainer + ' ' + Oro.Messages.topMessageHolder).append(newMessage);
        $(Oro.Messages.topMessageContainer).show();
    }
});

