<div class="row">
	<div class="col-md-10  col-md-offset-1">
		<h2>Paiment de votre compte <?php echo $request->user->compte == USER_PREMIUM ? 'premium' : 'pro'; ?></h2>
		<div>
			<p>Vous avez décicdé de souscrire au compte <?php echo $request->user->compte == USER_PREMIUM ? 'premium' : 'pro'; ?> pour une durée de 1 an.</p>
			<p>Pour pouvoir accèder au fonctionnalité du compte <?php echo $request->user->compte == USER_PREMIUM ? 'premium' : 'pro'; ?>, vous devez réglé la somme de 
			<?php echo $request->prix; ?> €</p>
			<p>Si vous ne souhaitez pas souscrire au compte premium mais que vous souhaitez tout de même créer votre compte, cliquer sur le bouton "Créer un compte classique"</p>
			<p>Si vous souhaitez annuler la création de votre compte cliquer sur annuler</p>
		</div>
		<div class="row">
			<div class="col-md-offset-2 col-md-4">
				<input id="command" class="btn btn-primary" type="submit" value="Créer un compte classique">
			</div>
			<div class="col-md-4">
				<input id="command" class="btn btn-primary" type="submit" value="Annuler">
			</div>
		</div>
		<div>
			<input id="accept_cgv" type="checkbox" /> Avant de continuer, vous devez accepter les <a href="?action=cgv" target="_blank">conditions générales de vente</a>.
		</div>
		<div id="accept_cgv_error_message" class="alert alert-danger" role="alert">
			<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
			<span class="sr-only">Error:</span>
			Vous devez accepter les conditions générales de vente pour pouvoir continuer
		</div>
		<div id="paiementsForm" class="row" style="display : none;">
			<div class="col-md-offset-2 col-md-4">
				<form id="payPaypal" action="?controler=compte&action=paypal" method="POST">
					<input id="command" class="btn btn-primary" type="submit" value="Payer avec paypal">
				</form>
			</div>
			<div class="col-md-4">
				<form id="payCard" action="?controler=compte&action=stripe" method="POST">
					<script
						src="https://checkout.stripe.com/checkout.js" class="stripe-button"
						data-email="<?php echo $request->user->email; ?>"
						data-allow-remember-me="false"
						data-label="Payer par carte"
						data-key="<?php echo STRIPE_PUBLIC_KEY; ?>"
						data-amount="<?php echo ($request->prix * 100); ?>"
						data-name="Fiche a partager"
						data-description="Paiement du compte <?php echo $request->user->compte == USER_PREMIUM ? 'premium' : 'pro'; ?>"
						data-image="/web/res/img/logo_mail.png"
						data-locale="auto"
						data-zip-code="true"
						data-currency="eur">
					</script>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$("#accept_cgv").click(function () {
		if ($("#accept_cgv").is(":checked")) {
			$("#accept_cgv_error_message").hide();
			$("#paiementsForm").show();
			$("#payCard .stripe-button-el span").css("min-height", "0");
			$("#payCard .stripe-button-el").removeClass("stripe-button-el").addClass("btn btn-primary");
		} else {
			$("#accept_cgv_error_message").show();
			$("#paiementsForm").hide();
		}
	});
</script>