<?php
/**
 * Tracking codes and cookie consent, on every Marccent site (Appearance → Marccent theme → Tracking and cookies).
 *
 * Tracking codes (the marccent_tracking option): raw HTML pasted from Google Tag Manager, Analytics, Meta and the like,
 * in the three places they ask for: in <head>, right after <body> opens (GTM's noscript) and before </body>. Saved only
 * by someone allowed to post unfiltered HTML.
 *
 * Cookie consent (the marccent_consent option): Google's Consent Mode v2 and a small banner. On, every page starts with
 * ads and analytics storage denied, before any tracking code runs; the banner asks once, and the visitor's answer (the
 * marccent_consent cookie, six months) updates Google's tags at once and on every later page. Tags that don't read
 * Consent Mode (Meta's pixel, heatmaps, chat widgets) can wait: with "Hold the tracking codes", the codes are printed
 * inert (<template>) and only run once the visitor accepts. A link to #cookie-settings anywhere reopens the banner.
 *
 */

defined( 'ABSPATH' ) || exit;

const MARCCENT_WP_TRACKING = 'marccent_tracking';
const MARCCENT_WP_CONSENT  = 'marccent_consent';

// Where each code goes: [ key => [ hook, priority ] ].
const MARCCENT_WP_TRACKING_HOOKS = array(
	'head'       => array( 'wp_head', 1 ),
	'body_open'  => array( 'wp_body_open', 1 ),
	'body_close' => array( 'wp_footer', 99 ),
);

// Both options exist (autoloaded), so a site that never saved them doesn't look for them with a query on every page.
add_action( 'init', function () {
	foreach ( array( MARCCENT_WP_TRACKING, MARCCENT_WP_CONSENT ) as $option ) {
		if ( false === get_option( $option, false ) ) {
			add_option( $option, array() );
		}
	}
} );

/* Tracking codes */

function marccent_wp_tracking(): array {
	$saved = get_option( MARCCENT_WP_TRACKING, array() );
	return array_merge( array_fill_keys( array_keys( MARCCENT_WP_TRACKING_HOOKS ), '' ), is_array( $saved ) ? $saved : array() );
}

// Scripts are the point, so the HTML is stored as pasted, and only by someone allowed to post unfiltered HTML.
function marccent_wp_tracking_sanitize( $input ): array {
	$old = marccent_wp_tracking();
	if ( ! current_user_can( 'unfiltered_html' ) ) {
		add_settings_error( MARCCENT_WP_TRACKING, 'unfiltered_html', __( 'Only an administrator who can post unfiltered HTML can change the tracking codes; they were kept.', 'marccent-wp' ) );
		return $old;
	}
	$out = array();
	foreach ( $old as $key => $value ) {
		$out[ $key ] = trim( (string) wp_unslash( $input[ $key ] ?? $value ) );
	}
	return $out;
}

// On the front end only: not in feeds, embeds or the customizer's preview.
foreach ( MARCCENT_WP_TRACKING_HOOKS as $marccent_wp_key => [ $marccent_wp_hook, $marccent_wp_priority ] ) {
	add_action( $marccent_wp_hook, function () use ( $marccent_wp_key ) {
		if ( is_admin() || is_feed() || is_embed() || is_customize_preview() ) {
			return;
		}
		// Cookie consent may hold it until the visitor accepts.
		$code = (string) apply_filters( 'marccent_tracking_code', marccent_wp_tracking()[ $marccent_wp_key ], $marccent_wp_key );
		if ( '' !== $code ) {
			echo "\n" . $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- an administrator's tracking code, as pasted.
		}
	}, $marccent_wp_priority );
}

/* Cookie consent */

function marccent_wp_consent_defaults(): array {
	return array(
		'on'     => false,
		'hold'   => false,
		'text'   => __( 'We use cookies to see how the site is used and to measure our ads. Is that OK?', 'marccent-wp' ),
		'accept' => __( 'Accept', 'marccent-wp' ),
		'reject' => __( 'No thanks', 'marccent-wp' ),
		'policy' => '',
	);
}

function marccent_wp_consent(): array {
	$saved = get_option( MARCCENT_WP_CONSENT, array() );
	return array_merge( marccent_wp_consent_defaults(), is_array( $saved ) ? $saved : array() );
}

function marccent_wp_consent_sanitize( $input ): array {
	$input    = is_array( $input ) ? $input : array();
	$defaults = marccent_wp_consent_defaults();
	return array(
		'on'     => ! empty( $input['on'] ),
		'hold'   => ! empty( $input['hold'] ),
		'text'   => sanitize_textarea_field( (string) wp_unslash( $input['text'] ?? '' ) ) ?: $defaults['text'],
		'accept' => sanitize_text_field( (string) wp_unslash( $input['accept'] ?? '' ) ) ?: $defaults['accept'],
		'reject' => sanitize_text_field( (string) wp_unslash( $input['reject'] ?? '' ) ) ?: $defaults['reject'],
		'policy' => esc_url_raw( (string) wp_unslash( $input['policy'] ?? '' ) ),
	);
}

function marccent_wp_consent_front(): bool {
	return marccent_wp_consent()['on'] && ! is_admin() && ! is_feed() && ! is_embed() && ! is_customize_preview();
}

// First thing in <head>, before the tracking codes (priority 1): denied by default, or the visitor's earlier answer
// (its cookie; mcl_consent is the name Marccent Local gave it).
add_action( 'wp_head', function () {
	if ( ! marccent_wp_consent_front() ) {
		return;
	}
	?>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('consent', 'default', {ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied', analytics_storage: 'denied', functionality_storage: 'granted', security_storage: 'granted', wait_for_update: 500});
(function () {
	var m = document.cookie.match(/(?:^|; )(?:marccent|mcl)_consent=(yes|no)/);
	if (m && m[1] === 'yes') {
		gtag('consent', 'update', {ad_storage: 'granted', ad_user_data: 'granted', ad_personalization: 'granted', analytics_storage: 'granted'});
	}
	window.marccentConsent = m ? m[1] : '';
})();
</script>
	<?php
}, 0 );

// "Hold the tracking codes": inert until the visitor accepts (the banner's script runs them).
add_filter( 'marccent_tracking_code', function ( string $code ) {
	if ( '' === $code || ! marccent_wp_consent_front() || ! marccent_wp_consent()['hold'] ) {
		return $code;
	}
	return '<template data-marccent-consent>' . $code . '</template>';
} );

// The banner and its script, at the end of the page. Its look is a stopgap until marccent-theme has a consent
// component (marccent-theme/TICKETS.md T-002).
add_action( 'wp_footer', function () {
	if ( ! marccent_wp_consent_front() ) {
		return;
	}
	$c = marccent_wp_consent();
	?>
<div id="marccent-consent" class="marccent-consent" role="dialog" aria-live="polite" aria-label="<?php esc_attr_e( 'Cookies', 'marccent-wp' ); ?>" hidden>
	<p><?php echo esc_html( $c['text'] ); ?><?php if ( $c['policy'] ) : ?> <a href="<?php echo esc_url( $c['policy'] ); ?>"><?php esc_html_e( 'Privacy policy', 'marccent-wp' ); ?></a><?php endif; ?></p>
	<div class="marccent-consent__actions">
		<button type="button" class="btn" data-consent="no"><?php echo esc_html( $c['reject'] ); ?></button>
		<button type="button" class="btn primary" data-consent="yes"><?php echo esc_html( $c['accept'] ); ?></button>
	</div>
</div>
<script>
(function () {
	var box = document.getElementById('marccent-consent');
	var granted = {ad_storage: 'granted', ad_user_data: 'granted', ad_personalization: 'granted', analytics_storage: 'granted'};
	var denied = {ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied', analytics_storage: 'denied'};
	// The held codes, run: each script made anew (a script cloned from a template doesn't run).
	var release = function () {
		document.querySelectorAll('template[data-marccent-consent]').forEach(function (t) {
			var fragment = t.content.cloneNode(true);
			fragment.querySelectorAll('script').forEach(function (old) {
				var s = document.createElement('script');
				for (var i = 0; i < old.attributes.length; i++) s.setAttribute(old.attributes[i].name, old.attributes[i].value);
				s.text = old.text;
				old.replaceWith(s);
			});
			t.replaceWith(fragment);
		});
	};
	var answer = function (yes) {
		document.cookie = 'marccent_consent=' + (yes ? 'yes' : 'no') + ';path=/;max-age=15552000;SameSite=Lax' + (location.protocol === 'https:' ? ';Secure' : '');
		gtag('consent', 'update', yes ? granted : denied);
		dataLayer.push({event: 'marccent_consent', consent: yes ? 'yes' : 'no'});
		if (yes) release();
		box.hidden = true;
	};
	box.addEventListener('click', function (e) {
		var b = e.target.closest('[data-consent]');
		if (b) answer(b.dataset.consent === 'yes');
	});
	document.addEventListener('click', function (e) {
		var a = e.target.closest('a[href$="#cookie-settings"]');
		if (a) {
			e.preventDefault();
			box.hidden = false;
		}
	});
	if (window.marccentConsent === 'yes') release();
	else if (!window.marccentConsent) box.hidden = false;
})();
</script>
	<?php
}, 100 );

add_action( 'wp_enqueue_scripts', function () {
	if ( ! marccent_wp_consent()['on'] ) {
		return;
	}
	wp_add_inline_style( 'marccent', '.marccent-consent{position:fixed;inset:auto 1rem 1rem auto;z-index:9999;max-width:24rem;padding:1rem 1.25rem;border-radius:var(--radius,.75rem);background:var(--surface,#fff);color:var(--text,#111);box-shadow:0 10px 30px rgb(0 0 0/.2);font-size:.9rem}.marccent-consent[hidden]{display:none}.marccent-consent p{margin:0 0 .75rem}.marccent-consent__actions{display:flex;gap:.5rem;justify-content:flex-end}@media (max-width:30rem){.marccent-consent{inset:auto .5rem .5rem .5rem;max-width:none}}' );
}, 20 );

/* The settings: Appearance → Marccent theme → Tracking and cookies (inc/theme-options.php shows the tab) */

add_action( 'admin_init', function () {
	register_setting( 'marccent-tracking', MARCCENT_WP_TRACKING, array( 'type' => 'array', 'sanitize_callback' => 'marccent_wp_tracking_sanitize' ) );
	register_setting( 'marccent-tracking', MARCCENT_WP_CONSENT, array( 'type' => 'array', 'sanitize_callback' => 'marccent_wp_consent_sanitize' ) );
} );

function marccent_wp_tracking_page(): void {
	$codes  = marccent_wp_tracking();
	$places = array(
		'head'       => array( __( 'In <head>', 'marccent-wp' ), __( 'Google Tag Manager\'s script, gtag.js (Analytics, Ads), Meta Pixel, verification meta tags.', 'marccent-wp' ) ),
		'body_open'  => array( __( 'After <body>', 'marccent-wp' ), __( 'Right after the body opens: Google Tag Manager\'s <code>&lt;noscript&gt;</code> iframe.', 'marccent-wp' ) ),
		'body_close' => array( __( 'Before </body>', 'marccent-wp' ), __( 'Chat widgets, heatmaps and anything that shouldn\'t hold up the page.', 'marccent-wp' ) ),
	);
	$c    = marccent_wp_consent();
	$name = fn( $key ) => esc_attr( MARCCENT_WP_CONSENT . "[$key]" );

	echo '<form method="post" action="options.php">';
	settings_fields( 'marccent-tracking' );
	echo '<h2>' . esc_html__( 'Tracking codes', 'marccent-wp' ) . '</h2>';
	echo '<p>' . esc_html__( 'Paste each code as its provider gives it, script tags and all. They run on every page of the site.', 'marccent-wp' ) . '</p>';
	echo '<table class="form-table" role="presentation">';
	foreach ( $places as $key => [ $label, $help ] ) {
		printf(
			'<tr><th scope="row"><label for="marccent-tracking-%1$s">%2$s</label></th><td><textarea id="marccent-tracking-%1$s" name="%3$s" rows="5" class="large-text code" spellcheck="false">%4$s</textarea><p class="description">%5$s</p></td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( MARCCENT_WP_TRACKING . "[$key]" ),
			esc_textarea( $codes[ $key ] ),
			wp_kses_post( $help )
		);
	}
	echo '</table>';

	echo '<h2>' . esc_html__( 'Cookie consent', 'marccent-wp' ) . '</h2>';
	echo '<p>' . esc_html__( 'Google Consent Mode v2 with a small banner: Google’s tags (Analytics, Ads, Tag Manager) wait for the visitor’s answer and measure without cookies until then. Needed for visitors from the EU and UK, and for Google Ads’ audiences anywhere.', 'marccent-wp' ) . '</p>';
	echo '<table class="form-table" role="presentation">';
	printf( '<tr><th scope="row">%s</th><td><label><input type="checkbox" name="%s" value="1" %s> %s</label></td></tr>', esc_html__( 'Banner', 'marccent-wp' ), $name( 'on' ), checked( $c['on'], true, false ), esc_html__( 'Ask visitors, and start Google’s tags denied until they answer', 'marccent-wp' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in $name
	printf( '<tr><th scope="row">%s</th><td><label><input type="checkbox" name="%s" value="1" %s> %s</label><p class="description">%s</p></td></tr>', esc_html__( 'Other tags', 'marccent-wp' ), $name( 'hold' ), checked( $c['hold'], true, false ), esc_html__( 'Hold the tracking codes until the visitor accepts', 'marccent-wp' ), esc_html__( 'For tags that don’t read Consent Mode: Meta’s pixel, heatmaps, chat widgets. Google’s tags don’t need it.', 'marccent-wp' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	printf( '<tr><th scope="row"><label for="marccent-consent-text">%s</label></th><td><textarea id="marccent-consent-text" name="%s" rows="2" class="large-text">%s</textarea></td></tr>', esc_html__( 'Its words', 'marccent-wp' ), $name( 'text' ), esc_textarea( $c['text'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	printf( '<tr><th scope="row">%s</th><td><input name="%s" value="%s" aria-label="%s"> <input name="%s" value="%s" aria-label="%s"></td></tr>', esc_html__( 'Buttons', 'marccent-wp' ), $name( 'accept' ), esc_attr( $c['accept'] ), esc_attr__( 'Accept', 'marccent-wp' ), $name( 'reject' ), esc_attr( $c['reject'] ), esc_attr__( 'Reject', 'marccent-wp' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	printf( '<tr><th scope="row"><label for="marccent-consent-policy">%s</label></th><td><input id="marccent-consent-policy" name="%s" value="%s" class="regular-text" placeholder="/privacy-policy/"><p class="description">%s</p></td></tr>', esc_html__( 'Privacy policy', 'marccent-wp' ), $name( 'policy' ), esc_attr( $c['policy'] ), esc_html__( 'A link in the banner. Any link to #cookie-settings (in the footer, say) opens the banner again.', 'marccent-wp' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '</table>';
	submit_button();
	echo '</form>';
}

// Tools → Marccent export (Marccent Blocks): the codes, put back only by someone allowed to post them (or WP-CLI), and
// the banner.
add_filter( 'marccent_export_parts', function ( $parts ) {
	$parts['tracking'] = array(
		'label'  => __( 'Tracking codes', 'marccent-wp' ),
		'export' => 'marccent_wp_tracking',
		'import' => function ( array $data ) {
			if ( ! current_user_can( 'unfiltered_html' ) && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
				return new WP_Error( 'marccent_tracking', __( 'Only an administrator who can post unfiltered HTML can import tracking codes.', 'marccent-wp' ) );
			}
			$codes = array();
			foreach ( MARCCENT_WP_TRACKING_HOOKS as $key => $hook ) {
				$codes[ $key ] = trim( (string) ( $data[ $key ] ?? '' ) );
			}
			remove_all_filters( 'sanitize_option_' . MARCCENT_WP_TRACKING ); // checked above; WP-CLI has no user
			update_option( MARCCENT_WP_TRACKING, $codes );
		},
	);
	$parts['consent'] = array(
		'label'  => __( 'Cookie consent', 'marccent-wp' ),
		'export' => 'marccent_wp_consent',
		'import' => fn( array $data ) => update_option( MARCCENT_WP_CONSENT, marccent_wp_consent_sanitize( $data ) ),
	);
	return $parts;
} );
