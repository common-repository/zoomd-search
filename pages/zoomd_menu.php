<div class="wrap">
	<h1>Zoomd</h1>
	<form method="post" action="options.php">
		<?php
		settings_fields( 'Zoomd' );
		do_settings_sections( 'zoomd' );
		submit_button();
		?>
	</form>
</div>