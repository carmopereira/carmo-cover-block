( function () {
	'use strict';

	function applyResponsiveBackground() {
		const coverBlocks = document.querySelectorAll(
			'.wp-block-cover[data-mobile-bg]'
		);

		if ( coverBlocks.length === 0 ) {
			return;
		}

		const mediaQuery = window.matchMedia( '(max-width: 768px)' );

		coverBlocks.forEach( ( block ) => {
			block.dataset.desktopBg = block.style.backgroundImage;
		} );

		function handleViewportChange( e ) {
			coverBlocks.forEach( ( block ) => {
				const mobileBackgroundUrl =
					block.getAttribute( 'data-mobile-bg' );

				if ( e.matches && mobileBackgroundUrl ) {
					block.style.backgroundImage = `url(${ mobileBackgroundUrl })`;
				} else {
					block.style.backgroundImage =
						block.dataset.desktopBg || '';
				}
			} );
		}

		handleViewportChange( mediaQuery );

		if ( mediaQuery.addEventListener ) {
			mediaQuery.addEventListener( 'change', handleViewportChange );
		} else {
			mediaQuery.addListener( handleViewportChange );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener(
			'DOMContentLoaded',
			applyResponsiveBackground
		);
	} else {
		applyResponsiveBackground();
	}
} )();
