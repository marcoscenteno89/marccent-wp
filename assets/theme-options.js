/*
 * The theme's options page (Appearance → Marccent theme, inc/theme-options.php), plain JS on WordPress's own packages
 * (no build): everything marccent-theme lets a site set, beside a preview of the site that changes as you pick. Its
 * own page, not the editors, so the site's look isn't changed by accident: nothing is saved until Save is pressed.
 *
 * The preview's colors are worked out as Marccent Blocks' marccent_blocks_theme_vars does on the server (and the
 * package's paletteVars in the browser): the palette, the text on each fill (dark or light, whichever contrasts more).
 */
( function ( wp ) {
	const { createElement: el, Fragment, useEffect, useState } = wp.element;
	const { PanelBody, SelectControl, ToggleControl, CheckboxControl, TextareaControl, Button, Notice, BaseControl, __experimentalNumberControl: NumberControl, TextControl } = wp.components;
	const { base, options, saved, preview } = window.marccentThemeOptions;

	/* Colors: WCAG luminance and contrast, the text on a fill. */
	const lum = ( hex ) => {
		let h = hex.replace( '#', '' );
		if ( h.length === 3 ) h = [ ...h ].map( ( c ) => c + c ).join( '' );
		return [ 0, 2, 4 ].map( ( i ) => parseInt( h.slice( i, i + 2 ), 16 ) / 255 )
			.map( ( v ) => ( v <= 0.03928 ? v / 12.92 : ( ( v + 0.055 ) / 1.055 ) ** 2.4 ) )
			.reduce( ( a, v, i ) => a + v * [ 0.2126, 0.7152, 0.0722 ][ i ], 0 );
	};
	const ratio = ( a, b ) => { const [ hi, lo ] = [ lum( a ), lum( b ) ].sort( ( x, y ) => y - x ); return ( hi + 0.05 ) / ( lo + 0.05 ); };
	// Dark text when it contrasts more than light with every color given (Marccent Blocks' marccent_blocks_theme_vars).
	const onDark = ( ...colors ) => Math.min( ...colors.map( ( c ) => ( lum( c ) + 0.05 ) / 0.05 ) ) >= Math.min( ...colors.map( ( c ) => 1.05 / ( lum( c ) + 0.05 ) ) );

	// The theme in effect: the package's defaults with the site's on top (Marccent Blocks' marccent_blocks_theme).
	const effective = ( site ) => {
		const own = site.palettes || [];
		return { ...base, ...site, vars: { ...base.vars, ...site.vars }, palettes: [ ...own, ...base.palettes.filter( ( p ) => ! own.some( ( o ) => o.id === p.id ) ) ] };
	};
	const paletteOf = ( t ) => t.palettes.find( ( p ) => p.id === t.palette ) || t.palettes[ 0 ];
	const cssVars = ( t ) => {
		const p = paletteOf( t );
		const vars = {};
		if ( p ) {
			const text = ( ...c ) => ( onDark( ...c ) ? 'var(--dark)' : 'var(--light)' );
			Object.assign( vars, {
				'--primary': p.primary, '--primary-lighter': p.primary, '--primary-darker': p.primary,
				'--secondary': p.secondary, '--secondary-lighter': p.secondary, '--secondary-darker': p.secondary,
				'--gradient': 'linear-gradient(to right, var(--primary), var(--secondary))',
				'--gradient-lighter': 'linear-gradient(to right, var(--primary-lighter), var(--secondary-lighter))',
				'--gradient-darker': 'linear-gradient(to right, var(--primary-darker), var(--secondary-darker))',
				'--primary-text': text( p.primary ), '--secondary-text': text( p.secondary ), '--gradient-text': text( p.primary, p.secondary ),
			} );
			[ 'primary', 'secondary' ].forEach( ( n ) => [ 'lighter', 'darker' ].forEach( ( s ) => ( vars[ `--${ n }-${ s }-text` ] = vars[ `--${ n }-text` ] ) ) );
		}
		[ 'dark', 'light' ].forEach( ( m ) => t[ m ] && ( vars[ `--${ m }` ] = t[ m ] ) );
		return { ...vars, ...t.vars };
	};

	// The preview (a page of the site in an iframe) as the site will look: <html>'s attributes, and the colors on its
	// style attribute, where the server put the saved ones; a variable no longer set is taken off.
	const paint = ( doc, t ) => {
		if ( ! doc || ! doc.documentElement ) return;
		const html = doc.documentElement;
		html.id = 'site';
		html.dataset.design = t.design || 'modern';
		html.dataset.theme = t.isDark ? 'dark' : 'light';
		html.classList.toggle( 'glass', !! t.glass );
		if ( t.hover && t.hover !== 'none' ) html.dataset.hover = t.hover; else delete html.dataset.hover;
		const vars = cssVars( t );
		( html.marccentVars || [] ).filter( ( k ) => ! ( k in vars ) ).forEach( ( k ) => html.style.removeProperty( k ) );
		Object.entries( vars ).forEach( ( [ k, v ] ) => html.style.setProperty( k, v ) );
		html.marccentVars = Object.keys( vars );
	};

	const parse = ( json ) => { try { const v = JSON.parse( json || '{}' ); return v && typeof v === 'object' ? v : {}; } catch { return {}; } };
	const varsText = ( vars ) => Object.entries( vars || {} ).map( ( [ k, v ] ) => `${ k }: ${ v }` ).join( '\n' );
	const textVars = ( text ) => Object.fromEntries( text.split( '\n' ).map( ( l ) => l.match( /^\s*(--[a-z][a-z0-9-]*)\s*:\s*([^;{}<>]+?)\s*;?\s*$/ ) ).filter( Boolean ).map( ( m ) => [ m[ 1 ], m[ 2 ] ] ) );

	const Swatch = ( { color } ) => el( 'span', { style: { display: 'inline-block', width: 18, height: 18, borderRadius: 4, background: color, boxShadow: 'inset 0 0 0 1px rgb(0 0 0 / .2)' } } );
	const ColorInput = ( { label, value, onChange } ) => el( BaseControl, { label, __nextHasNoMarginBottom: true },
		el( 'div', { style: { display: 'flex', gap: 8, alignItems: 'center' } },
			el( 'input', { type: 'color', value: value || '#000000', onChange: ( e ) => onChange( e.target.value ), style: { width: 44, height: 32, padding: 0, border: 0, background: 'none' } } ),
			el( 'code', null, value ) ) );

	const Panel = ( { site, set } ) => {
		const t = effective( site );
		const [ vars, setVars ] = useState( null ); // the CSS variables as typed, while they're edited
		// What visitors may change: the full mini theme editor unless turned off, else the single controls ticked (as
		// Marccent Blocks' marccent_blocks_visitor_controls reads them).
		const visitor = { full: true, design: false, mode: false, glass: false, palette: false, ...site.visitor };
		const setVisitor = ( patch ) => set( { visitor: { ...visitor, ...patch } } );

		const p = paletteOf( t );
		const own = site.palettes || [];
		const isOwn = own.some( ( o ) => o.id === p.id );
		const editOwn = ( patch ) => set( { palettes: own.map( ( o ) => ( o.id === p.id ? { ...o, ...patch } : o ) ) } );
		const copy = () => {
			let n = own.length + 1;
			while ( t.palettes.some( ( x ) => x.id === `custom-${ n }` ) ) n++;
			set( { palettes: [ ...own, { id: `custom-${ n }`, primary: p.primary, secondary: p.secondary } ], palette: `custom-${ n }` } );
		};
		const remove = () => {
			const rest = own.filter( ( o ) => o.id !== p.id );
			set( { palettes: rest.length ? rest : undefined, palette: undefined } );
		};

		// Contrast (WCAG AA): the text on each fill, and the mode's text on the page.
		const page = t.isDark ? t.dark : t.light;
		const checks = [
			[ 'Text on the primary color', p.primary, onDark( p.primary ) ? t.dark : t.light ],
			[ 'Text on the secondary color', p.secondary, onDark( p.secondary ) ? t.dark : t.light ],
			[ 'The primary color on the page (links, outlines)', p.primary, page ],
		].map( ( [ what, a, b ] ) => [ what, ratio( a, b ) ] ).filter( ( [ , r ] ) => r < 4.5 );

		const design = options.designs.find( ( d ) => d.id === ( t.design || 'modern' ) );
		return el( Fragment, null,
			el( PanelBody, { title: 'Design style' },
				el( SelectControl, {
					label: 'Style', value: t.design || 'modern', __nextHasNoMarginBottom: true,
					options: options.designs.map( ( d ) => ( { value: d.id, label: d.title } ) ),
					help: design && design.description, onChange: ( design ) => set( { design } ),
				} ) ),
			el( PanelBody, { title: 'Colors' },
				el( 'div', { role: 'radiogroup', 'aria-label': 'Palette', style: { display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(52px, 1fr))', gap: 6, marginBottom: 12 } },
					t.palettes.map( ( x ) => el( Button, {
						key: x.id, role: 'radio', 'aria-checked': x.id === p.id, label: x.id, showTooltip: true,
						variant: x.id === p.id ? 'primary' : 'secondary', onClick: () => set( { palette: x.id } ),
						style: { justifyContent: 'center', gap: 2, height: 36, minWidth: 0, padding: 4 },
					}, el( Swatch, { color: x.primary } ), el( Swatch, { color: x.secondary } ) ) ) ),
				isOwn
					? el( Fragment, null,
						el( TextControl, { label: 'Palette name', value: p.id, disabled: true, __nextHasNoMarginBottom: true } ),
						el( ColorInput, { label: 'Primary', value: p.primary, onChange: ( primary ) => editOwn( { primary } ) } ),
						el( ColorInput, { label: 'Secondary', value: p.secondary, onChange: ( secondary ) => editOwn( { secondary } ) } ),
						el( 'div', { style: { display: 'flex', gap: 8, margin: '8px 0 16px' } },
							el( Button, { variant: 'secondary', onClick: copy }, 'Copy as a new palette' ),
							el( Button, { variant: 'tertiary', isDestructive: true, onClick: remove }, 'Delete' ) ) )
					: el( 'p', null, `“${ p.id }” comes with the theme. `, el( Button, { variant: 'link', onClick: copy }, 'Copy it to edit its colors' ) ),
				checks.map( ( [ what, r ] ) => el( Notice, { key: what, status: 'warning', isDismissible: false },
					`${ what }: ${ r.toFixed( 2 ) }:1, under WCAG AA's 4.5:1. Shift the color a shade.` ) ),
				el( ToggleControl, { label: 'Dark mode', checked: !! t.isDark, onChange: ( isDark ) => set( { isDark } ) } ),
				el( ColorInput, { label: 'Dark (text in light mode, the page in dark)', value: t.dark, onChange: ( dark ) => set( { dark } ) } ),
				el( ColorInput, { label: 'Light (the page in light mode, text in dark)', value: t.light, onChange: ( light ) => set( { light } ) } ) ),
			el( PanelBody, { title: 'Effects' },
				el( ToggleControl, { label: 'Glass', help: 'Frosted, see-through surfaces.', checked: !! t.glass, onChange: ( glass ) => set( { glass } ) } ),
				el( SelectControl, {
					label: 'Default hover', value: t.hover || 'none', __nextHasNoMarginBottom: true,
					help: 'For buttons and cards whose Hover setting is Default. Shown on the site, not in the editor.',
					options: [ { value: 'none', label: 'None' }, ...options.hovers.map( ( h ) => ( { value: h, label: h.replace( /-/g, ' ' ).replace( /^./, ( c ) => c.toUpperCase() ) } ) ) ],
					onChange: ( hover ) => set( { hover } ),
				} ) ),
			el( PanelBody, { title: 'Visitors' },
				el( 'p', null, 'What visitors can change for themselves, in the header. Their choice stays in their browser; the site’s own theme doesn’t change.' ),
				el( CheckboxControl, {
					label: 'The full mini theme editor', checked: visitor.full, onChange: ( full ) => setVisitor( { full } ), __nextHasNoMarginBottom: true,
					help: 'A Theme button that opens every control: design style, light / dark, glass, colors and Start over.',
				} ),
				el( 'p', { style: { margin: '16px 0 8px', fontWeight: 600 } }, visitor.full ? 'Or single controls, without the full editor:' : 'Single controls, each on its own in the header:' ),
				[ [ 'mode', 'Light / dark' ], [ 'glass', 'Glass' ], [ 'design', 'Design style' ], [ 'palette', 'Colors (the palettes)' ] ].map( ( [ key, label ] ) =>
					el( CheckboxControl, { key, label, checked: ! visitor.full && visitor[ key ], disabled: visitor.full, onChange: ( on ) => setVisitor( { [ key ]: on } ), __nextHasNoMarginBottom: true } ) ),
				! visitor.full && ! ( visitor.design || visitor.mode || visitor.glass || visitor.palette ) && el( 'p', { className: 'description' }, 'Nothing chosen: visitors see the site’s theme only.' ) ),
			el( PanelBody, { title: 'Advanced', initialOpen: false },
				el( TextareaControl, {
					label: 'CSS variables', rows: 6, __nextHasNoMarginBottom: true,
					help: 'One per line, e.g. --radius: 0.25rem. They go with the default design style (Modern).',
					value: vars !== null ? vars : varsText( site.vars ),
					onChange: ( text ) => { setVars( text ); const v = textVars( text ); set( { vars: Object.keys( v ).length ? v : undefined } ); },
					onBlur: () => setVars( null ),
				} ),
				el( NumberControl, {
					label: 'Visitors’ own theme lasts (days)', min: 0, max: 365, value: t.expiresDays,
					help: 'A visitor who changes the theme (the theme button) keeps it this long.',
					onChange: ( d ) => set( { expiresDays: Math.max( 0, Math.min( 365, parseInt( d, 10 ) || 0 ) ) } ),
				} ),
				el( Button, {
					variant: 'secondary', isDestructive: true, style: { marginTop: 12 },
					onClick: () => set( { design: undefined, isDark: undefined, glass: undefined, hover: undefined, dark: undefined, light: undefined, vars: undefined, expiresDays: undefined } ),
				}, 'Back to the theme’s defaults (keeps the palettes)' ) ) );
	};

	// The page: the options, a preview of the site, and Save (the site's setting marccent_defaults, through REST).
	const App = () => {
		const [ site, setSite ] = useState( () => parse( saved ) );
		const [ clean, setClean ] = useState( () => JSON.stringify( parse( saved ) ) );
		const [ status, setStatus ] = useState( null ); // { kind, text } after a save
		const [ saving, setSaving ] = useState( false );
		const [ frame, setFrame ] = useState( null );
		const dirty = JSON.stringify( site ) !== clean;

		const set = ( patch ) => {
			const next = { ...site, ...patch };
			Object.keys( next ).forEach( ( k ) => next[ k ] === undefined && delete next[ k ] );
			setSite( next );
			setStatus( null );
		};
		const save = () => {
			setSaving( true );
			wp.apiFetch( { path: '/wp/v2/settings', method: 'POST', data: { marccent_defaults: JSON.stringify( site ) } } )
				.then( ( r ) => {
					const back = parse( r.marccent_defaults );
					setSite( back );
					setClean( JSON.stringify( back ) );
					setStatus( { kind: 'success', text: 'Saved. The site uses the new theme now.' } );
					frame && frame.contentWindow.location.reload(); // the header's visitor buttons are the server's
				} )
				.catch( ( e ) => setStatus( { kind: 'error', text: 'Not saved: ' + ( e.message || 'the server said no.' ) } ) )
				.finally( () => setSaving( false ) );
		};

		// The preview follows every change (and paints itself again when it loads another page: onLoad).
		useEffect( () => { frame && paint( frame.contentDocument, effective( site ) ); } );
		// Leaving with changes not saved asks first.
		useEffect( () => {
			const warn = ( e ) => { if ( dirty ) { e.preventDefault(); e.returnValue = ''; } };
			window.addEventListener( 'beforeunload', warn );
			return () => window.removeEventListener( 'beforeunload', warn );
		}, [ dirty ] );

		return el( 'div', { className: 'marccent-theme-page' },
			el( 'div', { className: 'marccent-theme-page__options' },
				el( 'div', { className: 'marccent-theme-page__bar' },
					el( Button, { variant: 'primary', onClick: save, disabled: ! dirty || saving, isBusy: saving }, saving ? 'Saving…' : 'Save' ),
					el( Button, { variant: 'tertiary', onClick: () => { setSite( parse( clean ) ); setStatus( null ); }, disabled: ! dirty || saving }, 'Discard changes' ),
					dirty && el( 'span', { className: 'marccent-theme-page__dirty' }, 'Not saved yet' ) ),
				status && el( Notice, { status: status.kind, onRemove: () => setStatus( null ) }, status.text ),
				el( 'div', { className: 'components-panel' }, el( Panel, { site, set } ) ) ),
			el( 'div', { className: 'marccent-theme-page__preview' },
				el( 'iframe', { title: 'Preview of the site', src: preview, ref: setFrame, onLoad: ( e ) => paint( e.target.contentDocument, effective( site ) ) } ) ) );
	};

	wp.element.createRoot( document.getElementById( 'marccent-theme-options' ) ).render( el( App ) );
} )( window.wp );
