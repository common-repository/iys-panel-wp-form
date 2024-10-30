const config = window.im4wp_vars
const i18n = window.im4wp_vars.i18n
const ajaxurl = window.im4wp_vars.ajaxurl
const m = require('mithril')
const state = {
  working: false,
  done: false,
  success: false
}

function fetch (evt) {
  evt && evt.preventDefault()

  state.working = true
  state.done = false

  m.request({
    method: 'POST',
    url: ajaxurl + '?action=im4wp_renew_iyspanel_lists',
    timeout: 600000 // 10 minutes, matching max_execution_time
  }).then(function (data) {
    state.success = true

    if (data) {
      window.setTimeout(function () { window.location.reload() }, 3000)
    }
  }).catch(function (data) {
    state.success = false
  }).finally(function (data) {
    state.working = false
    state.done = true

    m.redraw()
  })

  m.request({
    method: 'POST',
    url: ajaxurl + '?action=im4wp_renew_brands',
    timeout: 600000 // 10 minutes, matching max_execution_time
  }).then(function (data) {
    state.success = true

    if (data) {
      window.setTimeout(function () { window.location.reload() }, 3000)
    }
  }).catch(function (data) {
    state.success = false
  }).finally(function (data) {
    state.working = false
    state.done = true

    m.redraw()
  })

  m.request({
    method: 'POST',
    url: ajaxurl + '?action=im4wp_renew_originators',
    timeout: 600000 // 10 minutes, matching max_execution_time
  }).then(function (data) {
    state.success = true

    if (data) {
      window.setTimeout(function () { window.location.reload() }, 3000)
    }
  }).catch(function (data) {
    state.success = false
  }).finally(function (data) {
    state.working = false
    state.done = true

    m.redraw()
  })
}

function view () {
  return m('form', {
    method: 'POST',
    onsubmit: fetch.bind(this)
  }, [
    m('p', [
      m('input', {
        type: 'submit',
        value: state.working ? i18n.fetching_iyspanel_lists : i18n.renew_iyspanel_lists,
        className: 'button',
        disabled: !!state.working
      }),
      m.trust(' &nbsp; '),

      state.working
        ? [
            m('span.im4wp-loader', 'Loading...'),
            m.trust(' &nbsp; ')
          ]
        : '',
      state.done
        ? [
            state.success ? m('em.im4wp-green', i18n.fetching_iyspanel_lists_done) : m('em.im4wp-red', i18n.fetching_iyspanel_lists_error)
          ]
        : ''
    ])
  ])
}

const mount = document.getElementById('im4wp-list-fetcher')
if (mount) {
  // start fetching right away when no lists but api key given
  if (config.iyspanel.api_connected && config.iyspanel.lists.length === 0) {
    fetch()
  }

  m.mount(mount, { view })
}
