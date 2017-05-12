define([], function() {
    return {
        config: function() {
            return {
                defaultController: {
                    module: 'pim/controller/template'
                },
                // pull in translation messages
                messages: this.messages
            }
        },
        setMessages: function(messages) {
          this.messages = messages
        }
    }
});
