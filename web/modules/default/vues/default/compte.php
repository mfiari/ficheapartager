<div class="row">
	<div class="col-md-10  col-md-offset-1">
		<h2>Mon compte</h2>
		<?php if ($request->errorMessage) : ?>
			<?php foreach ($request->errorMessage as $key => $value) : ?>
				<div class="alert alert-danger" role="alert">
					<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
					<span class="sr-only">Error:</span>
					<?php echo $value; ?>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
		<div class="col-md-9">
			<form method="post" enctype="x-www-form-urlencoded" id="subscribeForm" action="?action=compte">
				<fieldset>
					<div class="form-group">
						<label for="nom">Nom : </label>
						<input class="form-control" name="nom" type="text" value="<?php echo $request->user->nom; ?>" maxlength="32" required>
					</div>
					<div class="form-group">
						<label for="prenom">Prénom : </label>
						<input class="form-control" name="prenom" type="text" value="<?php echo $request->user->prenom; ?>" maxlength="32" required>
					</div>
					<div class="form-group">
						<label for="login">email : </label>
						<input class="form-control" name="login" type="email" value="<?php echo $request->user->login; ?>" maxlength="32" required>
					</div>
					<div class="form-group">
						<label for="rue">Adresse : </label>
						<span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" 
						title="L'adresse est facultative. Elle est utilisé pour facilité votre recherche."></span>
						<div class="search-block">
							<input id="full_address" class="form-control" name="adresse" type="text" value="<?php echo $request->user->rue.', '.$request->user->code_postal.' '.$request->user->ville; ?>" placeholder="Entrez votre adresse">
							<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>
						</div>
						<input id="rue" name="rue" type="text" value="<?php $request->user->rue; ?>" hidden="hidden">
						<input id="ville" name="ville" type="text" value="<?php echo $request->user->ville; ?>" hidden="hidden">
						<input id="code_postal" name="code_postal" type="text" value="<?php echo $request->user->code_postal; ?>" hidden="hidden">
					</div>
					<div class="form-group">
						<label for="telephone">Téléphone : </label>
						<span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" 
						title="Le numéro de téléphone est falcutatif, il nous sert à vous contacter en cas de problème sur votre commande. 
						En aucun cas il ne sera communiqué à des tiers"></span>
						<input class="form-control" name="telephone" type="text" value="<?php echo $request->user->telephone; ?>" maxlength="10" >
					</div>
					<button id="subscribe-button" class="btn btn-primary" type="button">Modifier</button>
				</fieldset>
			</form>
			<form method="post" enctype="x-www-form-urlencoded" id="subscribeForm" action="?action=compte">
				<h3>Modifier mon mot de passe</h3>
				<fieldset>
					<div class="form-group">
						<label for="password">Ancien Mot de passe : </label>
						<input class="form-control" id="password" name="password" type="password" maxlength="32" required>
					</div>
					<div class="form-group">
						<label for="password">Nouveau Mot de passe : </label>
						<input class="form-control" id="password" name="password" type="password" maxlength="32" required>
					</div>
					<div class="form-group">
						<label for="confirm_password">Confirmer Mot de passe : </label>
						<input class="form-control" name="confirm_password" type="password" maxlength="32" required>
					</div>
					<button id="subscribe-button" class="btn btn-primary" type="button">Modifier</button>
				</fieldset>
			</form>
			<form method="post" enctype="x-www-form-urlencoded" id="subscribeForm" action="?controler=paypal&action=premium_subscribe">
				<h3>Souscrire au compte premium</h3>
				<fieldset>
					<a href="?controler=paypal&action=premium_subscribe" id="subscribe-button" class="btn btn-primary" type="button">Souscrire</a>
				</fieldset>
			</form>
		</div>
		<div class="col-md-3">
			<ul>
				<li>Mon solde</li>
				<li><a href="?controler=compte&action=calendrier">Mon calendrier</a></li>
			</ul>
		</div>
	</div>
</div>