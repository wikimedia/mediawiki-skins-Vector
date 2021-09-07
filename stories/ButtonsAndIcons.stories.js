import wvui from '@wikimedia/wvui';
import Vue from 'vue';
import '@wikimedia/wvui/dist/wvui.css';
const wvuiIconAdd = 'M11 9V4H9v5H4v2h5v5h2v-5h5V9z';

export default {
	title: 'Icon and Buttons'
};

/**
 *
 * @typedef {Object} ButtonProps
 * @property {string} type
 * @property {string} action
 */
/**
 * @param {ButtonProps} props
 * @param {string} text
 * @return {string}
 */
function makeButtonLegacy( props, text ) {
	let typeClass = '';
	let iconClass = 'mw-ui-icon-add';
	switch ( props.type ) {
		case 'quiet':
			typeClass += ' mw-ui-quiet';
			break;
	}
	switch ( props.action ) {
		case 'progressive':
			typeClass += ' mw-ui-progressive';
			iconClass += '-progressive';
			break;
		case 'destructive':
			typeClass += ' mw-ui-destructive';
			iconClass += '-destructive';
			break;
	}
	if ( props.type === 'primary' ) {
		iconClass = 'mw-ui-icon-add-invert';
	}
	return `<button class="mw-ui-button ${typeClass}">
	<span class="mw-ui-icon ${iconClass}"
	></span>${text}
</button>`;
}

/**
 * @param {ButtonProps} props
 * @param {string} text
 * @param {string} icon
 * @return {string}
 */
function makeButton( props, text, icon ) {
	const el = document.createElement( 'div' );
	const vm = new Vue( {
		el,
		render: function ( createElement ) {
			return createElement( wvui.WvuiButton, {
				props
			}, [
				createElement( wvui.WvuiIcon, {
					props: {
						icon
					}
				} ),
				text
			] );
		}
	} );
	return `
	<tr>
		<td>${makeButtonLegacy( props, text )}</td>
		<td>${vm.$el.outerHTML}</td>
	</tr>`;
}

/**
 * @return {string}
 */
function makeIcon() {
	return `
	<tr>
		<td>
			<button class="mw-ui-icon mw-ui-icon-element mw-ui-icon-add
			mw-ui-button">Normal Icon</button>
		</td>
		<td>N/A</td>
	</tr>
	<tr>
		<td>
			<button class="mw-ui-icon mw-ui-icon-element mw-ui-icon-add
				mw-ui-button mw-ui-quiet">Quiet Icon</button>
		</td>
		<td>N/A</td>
	</tr>`;
}

/**
 *
 * @param {string[]} btns
 * @return {string}
 */
function makeButtons( btns ) {
	return `<table>
	<tbody>
		<tr>
			<th>Legacy</th>
			<th>WVUI</th>
		</tr>
		${btns.join( '\n' )}
	</tbody>
</table>`;
}

/**
 * @return {string}
 */
export const Button = () => makeButtons( [
	makeButton( {
		action: 'default',
		type: 'quiet'
	}, 'Quiet button', wvuiIconAdd ),
	makeButton( {
		action: 'progressive',
		type: 'quiet'
	}, 'Quiet progressive', wvuiIconAdd ),
	makeButton( {
		action: 'destructive',
		type: 'quiet'
	}, 'Quiet destructive', wvuiIconAdd ),
	makeButton( {
		action: 'default',
		type: 'normal'
	}, 'Normal', wvuiIconAdd ),
	makeButton( {
		type: 'primary',
		action: 'progressive'
	}, 'Progressive primary', wvuiIconAdd ),
	makeButton( {
		type: 'primary',
		action: 'destructive'
	}, 'Destructive primary', wvuiIconAdd ),
	makeIcon()
] );
