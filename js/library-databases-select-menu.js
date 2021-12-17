/**
 * Creates a select menu for navigating to databases.
 *
 * The lib_database_select outputs a <script> tag with all the JSON data
 * required to create the menu and calls createDatabaseSelectMenu().
 *
 * @param {string} dataId The id of the <script> tag with the JSON data for the
 * select menu.
 */
function createDatabaseSelectMenu( dataID ) {
	"use strict";
	const data = JSON.parse( document.getElementById( dataID ).text );

	var uniqueID      = data.uniqueID;
	var title         = data.title;
	var selectMessage = data.selectMessage;
	var menuData      = data.menuData;
	var select_id     = 'lib_databases_select_' + uniqueID;
	var containerID   = 'lib_databases_nav_' + uniqueID;

	var container = document.getElementById( containerID );
	var label = document.createElement( 'label' );
	var select_menu = document.createElement( 'select' );
	var select_prompt = document.createElement( 'option' );

	label.appendChild( document.createTextNode( title + ' ' ) );
	label.setAttribute( 'for', select_id );
	container.appendChild( label );
	select_menu.id = select_id;
	select_prompt.innerHTML = selectMessage;
	select_menu.appendChild( select_prompt );
	container.appendChild( select_menu );

	menuData.forEach( function ( value, index ) {
		let option = document.createElement( 'option' );
		option.innerHTML = value.title;
		option.setAttribute( 'value', value.url );
		if ( value.disabled ) {
			option.setAttribute( 'disabled', 'disabled' );
		}
		select_menu.appendChild( option );
	});
	select_menu.addEventListener( 'change', function (event ) {
		window.location = event.target.value;
	});
}
