const tables = require( '../../../resources/skins.vector.js/tables.js' ).init;

describe( 'tables', () => {
	test( 'wraps table with div', () => {
		document.body.innerHTML = `
			<section class="mw-parser-output">
				<table>
					<tbody><tr><th>table table table</th></tr></tbody>
				</table>
			</section>
		`;
		tables();

		expect( document.body.innerHTML ).toMatchSnapshot();
	} );

	test( 'wraps multiple table with div', () => {
		document.body.innerHTML = `
			<section class="mw-parser-output">
				<table>
					<tbody><tr><th>table table table</th></tr></tbody>
				</table>
				<table>
					<tbody><tr><th>table table table</th></tr></tbody>
				</table>
			</section>
		`;
		tables();

		expect( document.body.innerHTML ).toMatchSnapshot();
	} );

	test( 'doesnt wrap nested tables', () => {
		document.body.innerHTML = `
			<section class="mw-parser-output">
				<table>
					<tbody>
						<tr><th>table table table</th></tr>
						<tr><td><table><tbody><tr><th>table table table</th></tr></tbody></table><td></tr>
					</tbody>
				</table>

			</section>
		`;
		tables();

		expect( document.body.innerHTML ).toMatchSnapshot();
	} );
} );
