/*
 * The theme's block patterns (Appearance → Patterns, or the inserter's Patterns tab: "Marccent"): the look-only ones,
 * whose blocks live beside their markup in marccent-patterns (wp/patterns.cjs: marccent-theme#99, #102). The ones that
 * read a local business's data (hero, services, questions, contact, locations) are the Local business kit's
 * (marccent-kits), imported with it. Marccent Blocks' serializer writes them as validated blocks; this file then writes
 * patterns/*.php:
 *
 *   cd wp-content/plugins/marccent-blocks
 *   MARCCENT_SPEC=$PWD/../../themes/marccent-wp/tools/patterns.js MARCCENT_OUT=/tmp/patterns.json npm run serialize
 *   node ../../themes/marccent-wp/tools/patterns.js /tmp/patterns.json
 *
 * marccent-patterns is read from MARCCENT_PATTERNS, else from its checkout beside wp in the workspace (~/app).
 */
const path = require( 'path' );

const PATTERNS_REPO = process.env.MARCCENT_PATTERNS || path.join( __dirname, '..', '..', '..', '..', '..', 'marccent-patterns' );
const PATTERNS = require( path.join( PATTERNS_REPO, 'wp', 'patterns.cjs' ) );

module.exports = Object.fromEntries( PATTERNS.map( ( [ slug, , , , , blocks ] ) => [ slug, blocks ] ) );

// Run as a script with the serializer's output: patterns/<slug>.php, each with its header; a pattern no longer listed
// is removed.
if ( require.main === module ) {
	const fs = require( 'fs' );
	const out = JSON.parse( fs.readFileSync( process.argv[ 2 ], 'utf8' ) );
	const dir = path.join( __dirname, '..', 'patterns' );
	fs.mkdirSync( dir, { recursive: true } );
	const slugs = PATTERNS.map( ( [ slug ] ) => slug );
	for ( const file of fs.readdirSync( dir ).filter( ( f ) => f.endsWith( '.php' ) && ! slugs.includes( f.slice( 0, -4 ) ) ) ) {
		fs.rmSync( path.join( dir, file ) );
	}
	for ( const [ slug, title, categories, description, keywords ] of PATTERNS ) {
		fs.writeFileSync( path.join( dir, `${ slug }.php` ), `<?php
/**
 * Title: ${ title }
 * Slug: marccent-wp/${ slug }
 * Categories: ${ categories.join( ', ' ) }
 * Keywords: ${ keywords.join( ', ' ) }
 * Description: ${ description }
 *
 * Written by tools/patterns.js from marccent-patterns' wp/patterns.cjs; change it there.
 */
?>
${ out[ slug ] }
` );
	}
	console.log( `Wrote ${ PATTERNS.length } patterns to ${ dir }` );
}
