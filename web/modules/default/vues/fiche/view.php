<div class="row">
	<div class="col-md-10  col-md-offset-1">
		<span class="etoile" data-id="<?php echo $request->fiche->id; ?>" data-value="<?php echo $request->fiche->favorie; ?>">
			<?php if ($request->fiche->favorie) : ?>
				Retirer des favories
			<?php else : ?>
				Ajouter aux favories
			<?php endif; ?>
		</span>
		<h2><?php echo utf8_encode($request->fiche->titre); ?></h2>
		<div class="col-md-10  col-md-offset-1">
			<?php echo utf8_encode($request->fiche->text); ?>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(function() {
		$("span.etoile").click(function (evt) {
			var id = $(this).attr('data-id');
			var favorie = $(this).attr('data-value');
			var action = '';
			if (favorie == '1') {
				action = 'removeFavorie';
			} else {
				action = 'addFavorie';
			}
			$.ajax({
				type: "POST",
				url: '?controler=fiche&action='+action,
				dataType: "html",
				data: {id : id}
			}).done(function( msg ) {
				if (favorie == '1') {
					alert('Retirer des favories');
				} else {
					alert('Ajouter aux favories');
				}
			}).error(function(jqXHR, textStatus, errorThrown) {
				switch (jqXHR.status) {
					case 400 :
						$("#login-modal .modal-footer div.alert-danger span.message").html("Login ou mot de passe vide");
						break;
					case 403 :
						$("#login-modal .modal-footer div.alert-danger span.message").html("<p>Votre compte est désactivé</p><p>Vérifiez dans votre boite mail que vous avez bien reçu le mail de confirmation d'inscription</p><p>Sinon <a href='?controler=contact'>contactez-nous</a></p>");
						break;
					case 404 :
						$("#login-modal .modal-footer div.alert-danger span.message").html("Login ou mot de passe incorrect");
						break;
					default :
						$("#login-modal .modal-footer div.alert-danger span.message").html("Une erreur est survenu, veuillez réessayé.");
						break;
				}
				$("#login-modal .modal-footer div.alert-danger").css('display', 'block');
				hideLoading('loginButton', true);
			});
		});
	});
</script>