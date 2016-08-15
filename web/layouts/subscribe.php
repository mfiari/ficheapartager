<div id="subscribe-modal" class="modal fade" role="dialog">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Inscription</h4>
			</div>
			<div class="modal-body">
				<form method="post" enctype="x-www-form-urlencoded" id="subscribeForm" action="<?php echo WS_URL.'index.php?module=user&action=subscribe'; ?>">
					<div class="form-group">
						<label for="name">nom<span class="required">*</span> : </label>
						<input id="name" class="form-control" name="name" type="text" required>
					</div>
					<div class="form-group">
						<label for="email">email<span class="required">*</span> : </label>
						<input id="email" class="form-control" name="email" type="text" required>
					</div>
					<div class="form-group">
						<label for="password">Mot de passe<span class="required">*</span> : </label>
						<input id="password" class="form-control" name="password" type="password" required>
					</div>
					<div class="form-group">
						<label for="confirm_password">Confirmer Mot de passe<span class="required">*</span> : </label>
						<input id="confirm_password" class="form-control" name="confirm_password" type="password" required>
					</div>
					<div class="form-group">
						<label for="compte">Type de compte<span class="required">*</span> : </label>
						<input id="compte" class="form-control" name="compte" type="radio" value="CLASSIQUE">Classique (gratuit)<br />
						<input id="compte" class="form-control" name="compte" type="radio" value="PRO">Pro (2€)<br />
						<input id="compte" class="form-control" name="compte" type="radio" value="PREMIUM">Premium (5€)<br />
					</div>
					<button id="suscribeButton" class="btn btn-primary" type="submit">Inscription</button>
					<span style="display : none;" class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>
				</form>
			</div>
			<div class="modal-footer">
				<div style="display : none; text-align: center;" class="alert alert-danger" role="alert">
					<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
					<span class="sr-only">Error:</span>
					<span class="message"></span>
				</div>
			</div>
		</div>
	</div>
</div>