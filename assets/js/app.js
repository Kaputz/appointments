var $ = require('jquery');
global.$ = global.jQuery = $;
require('bootstrap-sass');
//require('moment');
require('fullcalendar'); 
require('jquery-datetimepicker'); 

//window.jQuery = require('jquery');
window.moment = require('moment');
//window.fullcalendar = require('fullcalendar');

$(document).ready(function() {
    $('[data-toggle="popover"]').popover();
});