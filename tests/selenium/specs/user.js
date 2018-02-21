'use strict';
const assert = require( 'assert' ),
	CreateAccountPage = require( '../../../../../tests/selenium/pageobjects/createaccount.page' ),
	EditPage = require( '../../../../../tests/selenium/pageobjects/edit.page' ),
	UserLoginPage = require( '../../../../../tests/selenium/pageobjects/userlogin.page' ),
	UserMessagePage = require( '../pageobjects/usermessage.page' );

describe( 'User', function () {

	var password,
		username;

	before( function () {
		// disable VisualEditor welcome dialog
		UserLoginPage.open();
		browser.localStorage( 'POST', { key: 've-beta-welcome-dialog', value: '1' } );
	} );

	beforeEach( function () {
		browser.deleteCookie();
		username = `User-${Math.random().toString()}`;
		password = Math.random().toString();
	} );

	it( 'should be able to view new message banner', function () {

		// create user
		browser.call( function () {
			return CreateAccountPage.apiCreateAccount( username, password );
		} );

		// create talk page with content
		browser.call( function () {
			return EditPage.apiEdit( 'User_talk:' + username, Math.random().toString() );
		} );

		// log in
		UserLoginPage.login( username, password );

		// check
		assert.equal( UserMessagePage.usermessage.getText(), 'You have a new message (last change).' );

	} );

} );
