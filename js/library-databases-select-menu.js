function createDatabaseSelectMenu(dataID) {
  const data = JSON.parse(document.getElementById(dataID).text);

  var uniqueID      = data['uniqueID'];
  var title         = data['title'];
  var selectMessage = data['selectMessage'];
  var menuData      = data['menuData'];
  var select_id     = 'lib_databases_select_' + uniqueID;
  var containerID   = 'lib_databases_nav_' + uniqueID;

  var container = document.getElementById(containerID);
  var select_menu = document.createElement('select');
  var select_prompt = document.createElement('option');

  container.appendChild(document.createTextNode(title + ' '));
  select_menu.id = select_id;
  select_prompt.innerHTML = selectMessage;
  select_menu.appendChild(select_prompt);
  container.appendChild(select_menu);

  menuData.forEach(function (value, index) {
    let option = document.createElement('option');
    option.innerHTML = value.title;
    option.setAttribute('value',value.url);
    if (value.disabled) {
      option.setAttribute('disabled','disabled');
    }
    select_menu.appendChild(option);
  });
  select_menu.addEventListener('change', function (event) {
    window.location = event.target.value;
  });
}
