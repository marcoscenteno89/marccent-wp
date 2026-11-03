/*
 * The theme's block patterns (Appearance → Editor, or the inserter's Patterns tab: "Marccent"): the sections every
 * Marccent site starts from (new-site-template/README.md in the wp repo), with placeholder words to replace. Repeated
 * things are Loops (Marccent Content), so a pattern's cards fill themselves from the site's pages or a field group's
 * rows. Marccent Blocks' serializer writes them as validated blocks; this file then writes patterns/*.php:
 *
 *   cd wp-content/plugins/marccent-blocks
 *   MARCCENT_SPEC=$PWD/../../themes/marccent-wp/tools/patterns.js MARCCENT_OUT=/tmp/patterns.json npm run serialize
 *   node ../../themes/marccent-wp/tools/patterns.js /tmp/patterns.json
 */
const B = ( name, attrs = {}, inner = [] ) => [ name, attrs, inner ];
const h = ( level, content, attrs = {} ) => B( 'core/heading', { level, content, ...attrs } );
const p = ( content, className ) => B( 'core/paragraph', className ? { content, className } : { content } );
const section = ( attrs, inner ) => B( 'marccent/section', { padY: 'pad-y-lg', ...attrs }, inner );
const grid = ( attrs, inner ) => B( 'marccent/grid', attrs, inner );
const stack = ( inner, gap = 'gap-sm' ) => grid( { cols: 1, gap, alignItems: 'items-start' }, inner );
const card = ( inner, attrs = {} ) => B( 'marccent/card', { hover: '', ...attrs }, inner );
const icon = ( name, about, surface = 'bg-primary' ) => B( 'marccent/icon', { icon: name, family: 'fa-solid', surface, label: `${ about } icon` } );
const button = ( label, href, ic = 'arrow-right', color = 'primary' ) => B( 'marccent/button', { label, href, icon: ic, color } );
const buttons = ( ...b ) => grid( { layout: 'row-wrap', gap: 'gap-sm' }, b );
const loop = ( attrs, item ) => B( 'marccent/loop', attrs, item );
const field = ( key, attrs = {} ) => B( 'marccent/field', { key, ...attrs } );
const shortcode = ( text ) => B( 'core/shortcode', { text } );

// [ slug, title, categories, description, keywords, blocks ]
const PATTERNS = [
	[ 'hero', 'Hero: heading, lead and calls to action', [ 'banner', 'marccent' ], 'The top of a page: what the business does and where, and the two things a visitor can do next.', [ 'hero', 'header', 'cta' ], [
		section( { surface: 'bg-secondary' }, [
			grid( { cols: 2, gap: 'gap-lg', alignItems: 'items-center' }, [
				stack( [
					h( 1, 'What you do, in the city you serve' ),
					p( 'One or two sentences a visitor (and an answer engine) can quote: what the business does, for whom, and where.', 'fs-lg' ),
					buttons( button( 'Request a quote', '#form', 'file-signature', 'primary' ), button( 'Call (555) 555-0100', 'tel:+15555550100', 'phone', 'light' ) ),
				] ),
				B( 'core/image', { sizeSlug: 'large', className: 'round overflow-hidden' } ),
			] ),
		] ),
	] ],
	[ 'services', 'Services: a card for each service page', [ 'marccent', 'text' ], 'The pages under Services, a card each (their featured image, title and excerpt), from a Loop: a new service page shows up by itself.', [ 'services', 'cards', 'loop' ], [
		section( {}, [
			stack( [ h( 2, 'Our services' ), p( 'A line on how the services fit together.' ) ] ),
			loop( { postType: 'page', parent: 'services', orderBy: 'menu_order', order: 'asc', perPage: 12, cols: 3, excludeCurrent: true }, [
				card( [
					field( 'image' ),
					field( 'title', { tag: 'h3', link: true } ),
					field( 'excerpt', { format: 'words:24' } ),
					button( 'More about {{ title }}', '{{ link }}', 'arrow-right', 'primary' ),
				] ),
			] ),
		] ),
	] ],
	[ 'features', 'Why us: three points with icons', [ 'marccent', 'text' ], 'Three reasons to choose the business, each with an icon.', [ 'features', 'benefits', 'icons' ], [
		section( { surface: 'bg' }, [
			h( 2, 'Why customers choose us' ),
			grid( { cols: 3, gap: 'gap-lg' }, [
				[ 'shield-halved', 'Licensed and insured', 'Say what that means for the customer.' ],
				[ 'clock', 'On time', 'A promise the business keeps.' ],
				[ 'star', 'Rated by neighbors', 'A number people can check.' ],
			].map( ( [ ic, title, text ] ) => card( [ icon( ic, title ), h( 3, title ), p( text ) ] ) ) ),
		] ),
	] ],
	[ 'gallery', 'Gallery: a field group’s photos', [ 'gallery', 'marccent' ], 'The gallery page’s Photos rows (a repeating field group: image, caption), in a grid, with a filter visitors can use. Needs the Photos group on the gallery page.', [ 'gallery', 'photos', 'loop' ], [
		section( {}, [
			h( 2, 'Our work' ),
			loop( { source: 'photos', from: 'gallery', perPage: 24, cols: 3, gap: 'gap-sm', paginate: true }, [
				field( 'image' ),
				field( 'caption', { tag: 'p' } ),
			] ),
		] ),
	] ],
	[ 'faq', 'Questions and answers', [ 'marccent', 'text' ], 'Questions customers ask, two columns. Put the same questions in the page’s FAQ (search reads them as FAQ schema).', [ 'faq', 'questions' ], [
		section( { surface: 'bg' }, [
			h( 2, 'Questions' ),
			grid( { cols: 2, gap: 'gap-lg', alignItems: 'items-start' }, [ 1, 2, 3, 4 ].map( ( n ) => stack( [ h( 3, `A question customers ask (${ n })?` ), p( 'The answer, in two or three plain sentences.' ) ] ) ) ),
		] ),
	] ],
	[ 'cta', 'Call to action band', [ 'call-to-action', 'marccent' ], 'A band asking for the next step, in the brand color.', [ 'cta', 'quote', 'call' ], [
		section( { surface: 'bg-primary' }, [
			grid( { cols: 2, gap: 'gap-lg', alignItems: 'items-center' }, [
				stack( [ h( 2, 'Ready to start?' ), p( 'Free estimates, an answer within a day.' ) ] ),
				buttons( button( 'Request a quote', '#form', 'file-signature', 'light' ), button( 'Call (555) 555-0100', 'tel:+15555550100', 'phone', 'secondary' ) ),
			] ),
		] ),
	] ],
	[ 'contact', 'Contact: a form, the hours and the phone', [ 'marccent', 'contact' ], 'A quote form beside the business’s hours (Settings → Business) and whether it’s open now.', [ 'contact', 'form', 'hours' ], [
		section( { anchor: 'form' }, [
			grid( { cols: 2, gap: 'gap-lg', alignItems: 'items-start' }, [
				stack( [
					h( 2, 'Get a free quote' ),
					B( 'marccent/form', { submitLabel: 'Send', successMessage: 'Thanks! We got it and will be in touch within a day.' }, [
						B( 'marccent/form-field', { name: 'name', label: 'Name', required: true } ),
						B( 'marccent/form-field', { name: 'phone', label: 'Phone', type: 'tel', required: true } ),
						B( 'marccent/form-field', { name: 'email', label: 'Email', type: 'email' } ),
						B( 'marccent/form-field', { name: 'message', label: 'What do you need?', type: 'textarea' } ),
						B( 'marccent/form-field', { name: 'photo', label: 'A photo (optional)', type: 'file', accept: 'image/*' } ),
					] ),
				] ),
				stack( [
					h( 3, 'Hours' ),
					shortcode( '[open_now]' ),
					shortcode( '[business_hours]' ),
				] ),
			] ),
		] ),
	] ],
	[ 'locations', 'Locations', [ 'marccent', 'contact' ], 'The business’s offices or shops (Settings → Business → Locations), each with its address, phone, hours and directions.', [ 'locations', 'offices', 'map' ], [
		section( {}, [ h( 2, 'Where to find us' ), shortcode( '[business_locations]' ) ] ),
	] ],
];

module.exports = Object.fromEntries( PATTERNS.map( ( [ slug, , , , , blocks ] ) => [ slug, blocks ] ) );

// Run as a script with the serializer's output: patterns/<slug>.php, each with its header.
if ( require.main === module ) {
	const fs = require( 'fs' );
	const path = require( 'path' );
	const out = JSON.parse( fs.readFileSync( process.argv[ 2 ], 'utf8' ) );
	const dir = path.join( __dirname, '..', 'patterns' );
	fs.mkdirSync( dir, { recursive: true } );
	for ( const [ slug, title, categories, description, keywords ] of PATTERNS ) {
		fs.writeFileSync( path.join( dir, `${ slug }.php` ), `<?php
/**
 * Title: ${ title }
 * Slug: marccent-wp/${ slug }
 * Categories: ${ categories.join( ', ' ) }
 * Keywords: ${ keywords.join( ', ' ) }
 * Description: ${ description }
 *
 * Written by tools/patterns.js; change it there.
 */
?>
${ out[ slug ] }
` );
	}
	console.log( `Wrote ${ PATTERNS.length } patterns to ${ dir }` );
}
