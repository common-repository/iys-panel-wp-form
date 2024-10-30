const context = document.getElementById('im4wp-admin')
const listInputs = context.querySelectorAll('.im4wp-list-input')
const lists = window.im4wp_vars.iyspanel.lists
let selectedLists = []
const listeners = {}

// functions
function getSelectedListsWhere (searchKey, searchValue) {
  return selectedLists.filter(function (el) {
    return el[searchKey] === searchValue
  })
}

function getSelectedLists () {
  return selectedLists
}

function updateSelectedLists () {
  selectedLists = [];

  [].forEach.call(listInputs, function (input) {
    // skip unchecked checkboxes
    if (typeof (input.checked) === 'boolean' && !input.checked) {
      return
    }

    if (typeof (lists[input.value]) === 'object') {
      selectedLists.push(lists[input.value])
    }
  })

  toggleVisibleLists()
  emit('selectedLists.change', [selectedLists])
  return selectedLists
}

function toggleVisibleLists () {
  const rows = document.querySelectorAll('.lists--only-selected > *');
  [].forEach.call(rows, function (el) {
    const listId = el.getAttribute('data-list-id')
    const isSelected = getSelectedListsWhere('id', listId).length > 0
    el.style.display = isSelected ? '' : 'none'
  })
}

function emit (event, args) {
  listeners[event] = listeners[event] || []
  listeners[event].forEach(f => f.apply(null, args))
}

function on (event, func) {
  listeners[event] = listeners[event] || []
  listeners[event].push(func)
}

[].forEach.call(listInputs, el => {
  el.addEventListener('change', updateSelectedLists)
})
updateSelectedLists()

module.exports = {
  getSelectedLists,
  on
}
