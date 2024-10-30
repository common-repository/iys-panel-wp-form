// dependencies
const tabs = require('./admin/tabs.js')
const settings = require('./admin/settings.js')
const helpers = require('./admin/helpers.js')

require('./admin/list-fetcher.js')
require('./admin/fields/iyspanel-api-key.js')
require('./admin/list-overview.js')
require('./admin/show-if.js')

// expose some things
window.im4wp = window.im4wp || {}
window.im4wp.helpers = helpers
window.im4wp.settings = settings
window.im4wp.tabs = tabs
