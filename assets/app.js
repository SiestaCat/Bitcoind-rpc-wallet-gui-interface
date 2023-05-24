/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import './styles/global.scss';

// start the Stimulus application
import './bootstrap';

import copy from 'copy-to-clipboard';

global.$ = global.jQuery = require('jquery');

global.Cookies = require('js-cookie');
global.copy = copy;

//Import modal image library
//Credits: Siniša Grubor https://codepen.io/sinisag/pen/vPEajE
import './styles/modal_image.css';
import './modal_image.js';

window.appLoaded = true;