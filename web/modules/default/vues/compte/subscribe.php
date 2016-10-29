<div class="row">
	<div class="col-md-10  col-md-offset-1">
		<h2>Inscription</h2>
		<?php if ($request->errorMessage && count($request->errorMessage) > 0) : ?>
			<div class="alert alert-danger" role="alert">
				<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
				<span class="sr-only">Error:</span>
				<?php foreach ($request->errorMessage as $message) : ?>
					<p><?php echo $message; ?></p>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<form method="post" enctype="x-www-form-urlencoded" id="subscribeForm" action="">
			<div class="form-group">
				<label for="name">nom<span class="required">*</span> : </label>
				<input id="name" class="form-control" name="name" type="text" required>
			</div>
			<div class="form-group">
				<label for="prenom">prenom<span class="required">*</span> : </label>
				<input id="prenom" class="form-control" name="prenom" type="text" required>
			</div>
			<div class="form-group">
				<label for="pseudo">pseudo<span class="required">*</span> : </label>
				<input id="pseudo" class="form-control" name="pseudo" type="text" required>
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
</div>