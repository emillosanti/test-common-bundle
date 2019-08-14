window.$ = window.jQuery = window.jquery = require("jquery");
require('jquery-ui');
require('jquery-ui/ui/widgets/slider');
require('bootstrap3');
require('select2');
window.moment = require('moment');
require('moment/locale/fr');
require('jquery-timepicker/jquery.timepicker');
require('bootstrap-datepicker');
require('bootstrap-datepicker/js/locales/bootstrap-datepicker.fr');
// @TODO move to automatic locale
$.fn.datepicker.defaults.weekStart = 1;
$.fn.datepicker.defaults.language = 'fr';
require('daterangepicker');
require('chart.js/dist/Chart.min');
require('chartjs-funnel');