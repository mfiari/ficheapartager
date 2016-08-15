
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
		console.log($("#loginForm"));
		$.ajax({
			type: "POST",
			url: WS_URL+"index.php?module=user&action=login",
			dataType: "html",
			data: $("#loginForm").serialize()
		}).done(function( msg ) {
			
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