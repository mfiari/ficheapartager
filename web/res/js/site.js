
$(function() {
	$("#logout").click(function (evt) {
		$.ajax({
			type: "POST",
			url: "logout.php",
			dataType: "html"
		}).done(function( msg ) {
			location.reload();
		});
	});
	
	$("#subscribeForm").submit(function(event) {
		event.preventDefault();
		console.log($("#subscribeForm"));
		$.ajax({
			type: "POST",
			url: $("#subscribeForm").attr('action'),
			dataType: "html",
			data: $("#subscribeForm").serialize()
		}).done(function( msg ) {
			
		});
	});
	
	$("#loginForm").submit(function(event) {
		event.preventDefault();
		showLoading('loginButton', true);
		console.log($("#loginForm"));
		$.ajax({
			type: "POST",
			url: $("#loginForm").attr('action'),
			dataType: "html",
			data: $("#loginForm").serialize()
		}).done(function( msg ) {
			location.reload();
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

function publicCategorieList(WS_URL, parentId) {
	$.ajax({
		type: "GET",
		url: WS_URL+"index.php?module=public_categorie&action=list",
		dataType: "html",
		data: {parent : parentId, ext : 'json'}
	}).done(function( msg ) {
		console.log(msg);
		var data = $.parseJSON(msg);
		$("#main-content .content-view").html('');
		if (parentId != 0) {
			var menuDiv = $('<div />');
			addParentPath(WS_URL, data.parent, menuDiv);
			$("#main-content .content-view").append(menuDiv);
		}
		for (var i = 0 ; i < data.categories.length ; i++) {
			$("#main-content .content-view").append(
				$('<div />').append(
					$('<span />').html(data.categories[i].nom).css('cursor', 'pointer')
				).append(
					$('<hr />')
				).click(
					{parentId : data.categories[i].id, WS_URL : WS_URL},
					function (event) {
						publicCategorieList(event.data.WS_URL, event.data.parentId);
					}
				)
			);
		}
	});
}

function addParentPath (WS_URL, parentCategorie, myElement) {
	if (parentCategorie.childrens) {
		myElement.append(
			$('<a />').html(parentCategorie.nom).click(
				{parentId : parentCategorie.id, WS_URL : WS_URL},
				function (event) {
					publicCategorieList(event.data.WS_URL, event.data.parentId);
				}
			)
		).append(
			$('<span />').html(' > ')
		);
		addParentPath (WS_URL, parentCategorie.childrens, myElement)
	} else {
		myElement.append(
			$('<span />').html(parentCategorie.nom)
		);
	}
}

function showLoading (divid, hide) {
	if (hide) {
		$("#"+divid).css('display', 'none');
	}
	$("#"+divid).after('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>');
}

function hideLoading (divid, show) {
	if (show) {
		$("#"+divid).css('display', 'block');
	}
	console.log($("#"+divid).next());
	$("#"+divid).next(".glyphicon-refresh").remove();
}