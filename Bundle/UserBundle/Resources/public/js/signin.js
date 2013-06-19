$(function() {
    $(window)
        .resize(function() {
            form = $('form.form-signin');
            var thisHeight = $(window).height()/2 - form.height()/2;
            if  (thisHeight > 40) {
                thisHeight = thisHeight -40;
            }
            form.css('margin-top', thisHeight );
        })
        .trigger('resize');

    var hashUrl = window.location.hash;
    var hashUrlTag = '#url=';
    if (hashUrl.length && hashUrl.match(hashUrlTag)) {
        if (hashUrl.indexOf('|')) {
            hashUrl = hashUrl.substring(0, hashUrl.indexOf('|'));
        }
        hashUrl = hashUrl.replace(hashUrlTag, '');
        var hashArray = hashUrl.split('php');
        if (hashArray[1]) {
            hashUrl = hashArray[1];
        }
        $('input[name="_target_path"]').val(hashUrl);
    }
});