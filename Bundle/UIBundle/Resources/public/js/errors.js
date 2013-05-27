$(document).ready(function() {
    function errorPopupPosition(){
        var oroHeight =($(window).height()/2 - $('div.popup-box-errors').height()) +'px';
        $('div.popup-box-errors').css({
            "margin-top" : oroHeight
        })
    }
    errorPopupPosition()
    $(window).resize(function(){
        errorPopupPosition()
    });
});