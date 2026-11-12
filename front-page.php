<?php
/**
 * Front page — photo tile wall.
 *
 * Displays the published posts' featured images as a uniform tile grid
 * (4 columns on desktop, responsive below). Tiles are not links:
 * clicking a photo does nothing.
 *
 * The server renders a generous loop; a small script then trims the
 * wall to exactly one screenful for the current viewport — photos
 * repeat only until the screen is filled, and no further.
 *
 * @package Multiverse
 */

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main">
		<?php
		$mv_posts = get_posts( array(
			'post_type'   => 'post',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby'     => 'date',
			'order'       => 'DESC',
		) );

		$mv_tiles = array();
		foreach ( $mv_posts as $mv_post ) {
			$mv_img = get_the_post_thumbnail_url( $mv_post->ID, 'full' );
			if ( $mv_img ) {
				$mv_tiles[] = array(
					'img'   => $mv_img,
					'title' => get_the_title( $mv_post->ID ),
				);
			}
		}

		if ( $mv_tiles ) {
			$mv_count  = count( $mv_tiles );
			$mv_target = 36; // generous loop; the script below trims to one screenful
			$mv_total  = max( $mv_count, (int) ceil( $mv_target / $mv_count ) * $mv_count );
			echo '<div class="mv-wall" data-unique="' . esc_attr( $mv_count ) . '">';
			for ( $mv_i = 0; $mv_i < $mv_total; $mv_i++ ) {
				$mv_t = $mv_tiles[ $mv_i % $mv_count ];
				echo '<div class="mv-tile"><img src="' . esc_url( $mv_t['img'] ) . '" alt="' . esc_attr( $mv_t['title'] ) . '" loading="lazy"><span class="mv-tile-title">' . esc_html( $mv_t['title'] ) . '</span></div>';
			}
			echo '</div>';
			?>
			<script>
			(function () {
				var wall = document.querySelector('.mv-wall');
				if (!wall) return;
				var unique = parseInt(wall.getAttribute('data-unique'), 10) || 0;
				var all = wall.querySelectorAll('.mv-tile');
				if (!unique || all.length < unique) return;
				var proto = [];
				for (var i = 0; i < unique; i++) proto.push(all[i].cloneNode(true));

				function tile(j) { return proto[j % unique].cloneNode(true); }

				function fit() {
					var cols = window.getComputedStyle(wall).gridTemplateColumns.split(' ').length;
					wall.innerHTML = '';
					for (var i = 0; i < cols; i++) wall.appendChild(tile(i));
					var rowH = wall.querySelector('.mv-tile').getBoundingClientRect().height;
					if (!rowH) return;
					var gap = parseFloat(window.getComputedStyle(wall).rowGap) || 0;
					var rows = Math.max(1, Math.ceil(wall.clientHeight / (rowH + gap)));
					var total = cols * rows;
					total = Math.max(total, unique);                    // at least one full set
					total = Math.ceil(total / unique) * unique;         // never end mid-set
					wall.innerHTML = '';
					for (var j = 0; j < total; j++) wall.appendChild(tile(j));
				}

				var t;
				window.addEventListener('resize', function () {
					clearTimeout(t);
					t = setTimeout(fit, 150);
				});
				fit();
			})();
			</script>
			<?php
		}
		?>
	</main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
