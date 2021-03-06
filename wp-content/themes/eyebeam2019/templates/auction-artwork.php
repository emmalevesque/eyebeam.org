<?php

$bid_increment = auction_bid_increment();
$current_bid = auction_get_current_bid();

?>
<div class="auction-artwork">
	<div class="module-title">
		<h3 class="eyebeam-sans">
			<a href="<?php the_permalink($post); ?>"><?php the_title(); ?></a>
		</h3>
	</div>
	<?php do_action('auction_feedback'); ?>
	<?php

	the_post_thumbnail('large');

	?>
	<div class="auction-artwork-description">
		<h4>Artist Experience</h4>
		<?php the_content(); ?>
	</div>
	<div class="auction-artwork-artist-bio">
		<h4>Artist bio</h4>
		<?php the_field('artist_bio'); ?>
	</div>
	<form action="<?php echo get_permalink($post); ?>" method="post" class="auction-bids">
		<h4>Bid on this experience</h4>
		<fieldset>
			<strong>Current bid</strong>
			<div class="current-bid">
				<?php if (! empty($current_bid['minimum_bid'])) { ?>
					<em>Be the first to bid on this!</em>
				<?php } else { ?>
					<?php auction_current_bid(); ?>
				<?php } ?>
			</div>
		</fieldset>
		<fieldset>
			<strong>Place a bid</strong><br>
			<label for="amount">Set your maximum bid amount</label>
			<div class="bid-amount">
				<input type="number" name="amount" step="<?php echo auction_bid_increment(); ?>" value="<?php echo auction_next_amount(); ?>" id="amount">
			</div>
			<div class="help">Your bid amount will increase in $<?php echo auction_bid_increment(); ?> increments until the maximum is reached.</div>
			<label for="name">Your name</label>
			<input type="text" name="name" value="<?php auction_name(); ?>">
			<label for="email">Email</label>
			<input type="email" name="email" value="<?php auction_email(); ?>">
			<label for="phone">Phone number</label>
			<input type="text" name="phone" value="<?php auction_phone(); ?>">
		</fieldset>
		<button type="submit">Place your bid</button>
	</form>
</div>
